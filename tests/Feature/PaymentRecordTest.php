<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\PaymentRecord;
use App\Models\Student;
use App\Models\StudentApplication;
use App\Models\User;
use App\Services\Consultancy\StudentApplicationWorkflow;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentRecordTest extends TestCase
{
    use RefreshDatabase;

    private User $consultant;

    private StudentApplication $application;

    private User $studentUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class]);
        Storage::fake('payment_receipts');
        Notification::fake();

        $this->consultant = $this->user('admission-consultant');
        $this->studentUser = $this->user('customer', ['email' => 'student@example.com']);
        $student = Student::query()->create([
            'first_name' => 'Pay', 'last_name' => 'Student', 'email' => 'student@example.com',
            'reference_code' => 'EGH-ST-PAY0000001', 'status' => Student::STATUS_ACTIVE,
        ]);
        $this->application = app(StudentApplicationWorkflow::class)->open($student, [
            'destination_country_id' => (int) Country::query()->where('iso2', 'SG')->value('id'),
            'assigned_consultant_id' => $this->consultant->id,
        ], null);
    }

    private function user(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(['email_verified_at' => now(), ...$attributes]);
        $user->assignRole($role);

        return $user;
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return [
            'payable_type' => 'student_application', 'payable_id' => $this->application->id,
            'amount' => '25000.00', 'currency' => 'BDT', 'method' => 'bkash', 'paid_on' => now()->toDateString(),
            'payer_reference' => 'TRX123456', 'purpose' => 'Consultancy service fee',
            'receipt' => UploadedFile::fake()->create('receipt.pdf', 50, 'application/pdf'), ...$overrides,
        ];
    }

    public function test_consultant_records_payment_with_private_receipt_pending_verification(): void
    {
        $this->actingAs($this->consultant)->post(route('admin.payments.store'), $this->payload())->assertRedirect();

        $payment = PaymentRecord::query()->sole();
        $this->assertSame(PaymentRecord::STATUS_PENDING, $payment->status);
        $this->assertSame($this->consultant->id, $payment->recorded_by_user_id);
        Storage::disk('payment_receipts')->assertExists($payment->receipt_path);
        $this->assertFalse(config('filesystems.disks.payment_receipts.serve'));
        $this->assertDatabaseHas('activity_logs', ['action' => 'payment.recorded']);

        $this->actingAs($this->consultant)->get(route('admin.payments.receipt', $payment))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->actingAs($this->studentUser)->get(route('admin.payments.receipt', $payment))->assertForbidden();

        $this->actingAs($this->studentUser)->get(route('student.applications.show', $this->application))
            ->assertOk()->assertDontSee($payment->reference)->assertSee('No verified payments yet.');
    }

    public function test_duplicate_transaction_bad_currency_and_spoofed_receipt_are_rejected(): void
    {
        $this->actingAs($this->consultant)->post(route('admin.payments.store'), $this->payload())->assertRedirect();
        $this->actingAs($this->consultant)->post(route('admin.payments.store'), $this->payload(['receipt' => null]))->assertSessionHasErrors('payer_reference');
        $this->actingAs($this->consultant)->post(route('admin.payments.store'), $this->payload(['payer_reference' => 'X1', 'currency' => 'XYZ']))->assertSessionHasErrors('currency');
        $this->actingAs($this->consultant)->post(route('admin.payments.store'), $this->payload(['payer_reference' => 'X2', 'receipt' => UploadedFile::fake()->create('r.pdf', 10, 'text/html')]))->assertSessionHasErrors('receipt');
        $this->actingAs($this->consultant)->post(route('admin.payments.store'), $this->payload(['payer_reference' => 'X3', 'paid_on' => now()->addDay()->toDateString()]))->assertSessionHasErrors('paid_on');

        $this->assertSame(1, PaymentRecord::query()->count());
    }

    public function test_verification_requires_a_second_person_and_then_shows_to_student(): void
    {
        $admin = $this->user('admin');
        $this->actingAs($admin)->post(route('admin.payments.store'), $this->payload())->assertRedirect();
        $payment = PaymentRecord::query()->sole();

        $this->actingAs($admin)->patch(route('admin.payments.verify', $payment), ['status' => 'verified'])->assertSessionHasErrors('status');
        $this->actingAs($this->consultant)->patch(route('admin.payments.verify', $payment), ['status' => 'verified'])->assertForbidden();

        $verifier = $this->user('admin');
        $this->actingAs($verifier)->patch(route('admin.payments.verify', $payment), ['status' => 'rejected'])->assertSessionHasErrors('verification_note');
        $this->actingAs($verifier)->patch(route('admin.payments.verify', $payment), ['status' => 'verified'])->assertRedirect();

        $payment->refresh();
        $this->assertSame(PaymentRecord::STATUS_VERIFIED, $payment->status);
        $this->assertSame($verifier->id, $payment->verified_by_user_id);
        $this->actingAs($verifier)->patch(route('admin.payments.verify', $payment), ['status' => 'rejected', 'verification_note' => 'x'])->assertSessionHasErrors('status');

        $this->actingAs($this->studentUser)->get(route('student.applications.show', $this->application))
            ->assertOk()->assertSee('BDT 25,000.00')->assertSee($payment->reference);
    }

    public function test_consultant_cannot_see_payments_of_unassigned_applications(): void
    {
        $admin = $this->user('admin');
        $otherStudent = Student::query()->create(['first_name' => 'O', 'last_name' => 'S', 'email' => 'o@example.com', 'reference_code' => 'EGH-ST-PAY0000002', 'status' => 'active']);
        $other = app(StudentApplicationWorkflow::class)->open($otherStudent, ['destination_country_id' => $this->application->destination_country_id], null);

        $this->actingAs($admin)->post(route('admin.payments.store'), $this->payload(['payable_id' => $other->id, 'payer_reference' => 'OTHER1']));
        $payment = PaymentRecord::query()->sole();

        $this->actingAs($this->consultant)->get(route('admin.payments.index'))->assertOk()->assertDontSee($payment->reference);
        $this->actingAs($this->consultant)->get(route('admin.payments.show', $payment))->assertForbidden();
        $this->actingAs($this->consultant)->get(route('admin.payments.create', ['payable_type' => 'student_application', 'payable_id' => $other->id]))->assertForbidden();
        $this->actingAs($this->user('crm-staff'))->get(route('admin.payments.index'))->assertForbidden();
    }
}
