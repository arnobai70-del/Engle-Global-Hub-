<?php

namespace Tests\Feature;

use App\Contracts\Payments\PaymentGateway;
use App\Enums\StudentApplicationStatus;
use App\Models\ApiToken;
use App\Models\Country;
use App\Models\Student;
use App\Models\User;
use App\Services\Consultancy\StudentApplicationWorkflow;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApiAndPaymentReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class]);
        Notification::fake();
    }

    private function student(string $email): array
    {
        $user = User::factory()->create(['email' => $email, 'password' => 'Secret-pass-123', 'email_verified_at' => now()]);
        $user->assignRole('customer');
        $student = Student::query()->create(['first_name' => 'Api', 'last_name' => 'Student', 'email' => $email, 'reference_code' => 'EGH-ST-'.strtoupper(substr(md5($email), 0, 10)), 'status' => 'active']);
        $student->forceFill(['user_id' => $user->id])->save();
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $application = app(StudentApplicationWorkflow::class)->open($student, ['destination_country_id' => (int) Country::query()->where('iso2', 'SG')->value('id')], $admin);
        app(StudentApplicationWorkflow::class)->transition($application, StudentApplicationStatus::EligibilityReview, $admin, 'Reviewing results.', 'SECRET-INTERNAL');

        return [$user, $application];
    }

    private function token(string $email): string
    {
        return $this->postJson(route('api.v1.auth.tokens.store'), ['email' => $email, 'password' => 'Secret-pass-123', 'device_name' => 'Pixel'])
            ->assertCreated()->json('access_token');
    }

    public function test_token_issue_and_student_data_access(): void
    {
        [$user, $application] = $this->student('api@example.com');
        [, $other] = $this->student('other@example.com');

        $this->postJson(route('api.v1.auth.tokens.store'), ['email' => 'api@example.com', 'password' => 'wrong', 'device_name' => 'x'])->assertUnprocessable();
        $this->getJson(route('api.v1.me'))->assertUnauthorized();

        $token = $this->token('api@example.com');
        $this->assertSame(1, ApiToken::query()->count());
        $this->assertNotSame($token, ApiToken::query()->sole()->token_hash);

        $headers = ['Authorization' => 'Bearer '.$token];
        $this->getJson(route('api.v1.me'), $headers)->assertOk()->assertJsonPath('data.email', 'api@example.com');
        $this->getJson(route('api.v1.applications.index'), $headers)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.reference', $application->reference);
        $this->getJson(route('api.v1.applications.show', $application), $headers)->assertOk()
            ->assertJsonPath('data.status.label', 'Eligibility Review')
            ->assertJsonPath('data.timeline.1.note', 'Reviewing results.')
            ->assertDontSee('SECRET-INTERNAL')->assertDontSee('stored_path');
        $this->getJson(route('api.v1.applications.show', $other), $headers)->assertNotFound();

        $this->deleteJson(route('api.v1.auth.tokens.destroy'), [], $headers)->assertNoContent();
        $this->app['auth']->forgetGuards();
        $this->getJson(route('api.v1.me'), $headers)->assertUnauthorized();
    }

    public function test_unverified_and_expired_tokens_are_rejected_and_login_is_rate_limited(): void
    {
        $unverified = User::factory()->create(['email' => 'new@example.com', 'password' => 'Secret-pass-123', 'email_verified_at' => null]);
        $this->postJson(route('api.v1.auth.tokens.store'), ['email' => 'new@example.com', 'password' => 'Secret-pass-123', 'device_name' => 'x'])->assertUnprocessable();

        $this->student('expiring@example.com');
        $token = $this->token('expiring@example.com');
        ApiToken::query()->update(['expires_at' => now()->subMinute()]);
        $this->getJson(route('api.v1.me'), ['Authorization' => 'Bearer '.$token])->assertUnauthorized();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('api.v1.auth.tokens.store'), ['email' => 'limit@example.com', 'password' => 'bad', 'device_name' => 'x']);
        }
        $this->postJson(route('api.v1.auth.tokens.store'), ['email' => 'limit@example.com', 'password' => 'bad', 'device_name' => 'x'])->assertTooManyRequests();
        $this->assertNotNull($unverified);
    }

    public function test_payment_webhook_is_closed_without_a_gateway_and_idempotent_with_one(): void
    {
        $this->postJson('/api/webhooks/payments/stripe', ['id' => 'evt_1'])->assertNotFound();

        $this->app->bind(PaymentGateway::class, fn () => new class implements PaymentGateway
        {
            public function key(): string
            {
                return 'testpay';
            }

            public function isConfigured(): bool
            {
                return true;
            }

            public function verifyWebhook(Request $request): ?array
            {
                $expected = hash_hmac('sha256', $request->getContent(), 'test-secret');

                return hash_equals($expected, (string) $request->header('X-Signature'))
                    ? ['id' => (string) $request->input('id'), 'type' => 'payment.succeeded', 'payment_reference' => null, 'status' => 'paid']
                    : null;
            }
        });

        $payload = json_encode(['id' => 'evt_123']);
        $send = fn (string $signature) => $this->call('POST', '/api/webhooks/payments/testpay', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_SIGNATURE' => $signature], $payload);

        $send('bad-signature')->assertStatus(400);
        $send(hash_hmac('sha256', $payload, 'test-secret'))->assertOk()->assertJsonPath('message', 'Received.');
        $send(hash_hmac('sha256', $payload, 'test-secret'))->assertOk()->assertJsonPath('message', 'Already processed.');
        $this->assertDatabaseCount('webhook_events', 1);
        $this->postJson('/api/webhooks/payments/otherpay', [])->assertNotFound();
    }
}
