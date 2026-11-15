<?php

namespace Tests\Feature\Consultancy;

use App\Models\Country;
use App\Models\Student;
use App\Models\StudentApplication;
use App\Models\User;
use App\Notifications\StudentApplicationMessageReceived;
use App\Services\Consultancy\StudentApplicationWorkflow;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentApplicationMessageTest extends TestCase
{
    use RefreshDatabase;

    private User $consultant;

    private User $studentUser;

    private StudentApplication $application;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class]);
        Notification::fake();

        $this->consultant = $this->user('admin');
        $this->studentUser = $this->user('customer', ['email' => 'student@example.com']);
        $student = Student::query()->create([
            'first_name' => 'Test', 'last_name' => 'Student', 'email' => 'student@example.com',
            'reference_code' => 'EGH-ST-MSG0000001', 'status' => Student::STATUS_ACTIVE,
        ]);
        $this->application = app(StudentApplicationWorkflow::class)->open($student, [
            'destination_country_id' => (int) Country::query()->where('iso2', 'SG')->value('id'),
            'assigned_consultant_id' => $this->consultant->id,
        ], $this->consultant);
    }

    private function user(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(['email_verified_at' => now(), ...$attributes]);
        $user->assignRole($role);

        return $user;
    }

    public function test_student_and_consultant_exchange_messages_and_notify_each_other(): void
    {
        $this->actingAs($this->studentUser)->post(route('student.applications.messages.store', $this->application), [
            'body' => 'When is the next intake deadline?',
        ])->assertRedirect(route('student.applications.show', $this->application));
        Notification::assertSentTo($this->consultant, StudentApplicationMessageReceived::class);

        $this->actingAs($this->consultant)->post(route('admin.student-applications.messages.store', $this->application), [
            'body' => 'We will confirm with the institution this week.',
        ])->assertRedirect();
        Notification::assertSentTo($this->studentUser, StudentApplicationMessageReceived::class);

        $this->actingAs($this->studentUser)->get(route('student.applications.show', $this->application))
            ->assertOk()
            ->assertSee('When is the next intake deadline?')
            ->assertSee('We will confirm with the institution this week.')
            ->assertDontSee($this->consultant->name);

        $this->actingAs($this->consultant)->get(route('admin.student-applications.show', $this->application))
            ->assertOk()->assertSee('When is the next intake deadline?');
        $this->assertDatabaseHas('activity_logs', ['action' => 'student_application.message_sent']);
    }

    public function test_messages_are_validated_and_restricted_to_the_owner(): void
    {
        $this->actingAs($this->studentUser)->post(route('student.applications.messages.store', $this->application), ['body' => ''])
            ->assertSessionHasErrors('body');

        $stranger = $this->user('customer');
        $this->actingAs($stranger)->post(route('student.applications.messages.store', $this->application), ['body' => 'hi'])
            ->assertForbidden();
        $this->actingAs($stranger)->post(route('admin.student-applications.messages.store', $this->application), ['body' => 'hi'])
            ->assertForbidden();

        $this->assertDatabaseCount('student_application_messages', 0);
    }

    public function test_notification_centre_lists_and_marks_read(): void
    {
        $this->studentUser->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => StudentApplicationMessageReceived::class,
            'data' => ['title' => 'New message on '.$this->application->reference, 'body' => 'From your consultant', 'url' => '/my-applications/'.$this->application->id],
        ]);

        $this->actingAs($this->studentUser)->get(route('notifications.index'))
            ->assertOk()->assertSee('New message on '.$this->application->reference);
        $this->actingAs($this->studentUser)->post(route('notifications.read'))->assertRedirect(route('notifications.index'));
        $this->assertSame(0, $this->studentUser->unreadNotifications()->count());
        $this->get(route('notifications.index'))->assertOk();
        auth()->logout();
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    }

    public function test_student_access_links_appear_in_existing_menus(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee(route('consultancy.singapore'), false)
            ->assertDontSee(route('student.applications.index'), false);

        $this->actingAs($this->studentUser)->get(route('dashboard'))->assertOk()
            ->assertSee(route('student.applications.index'), false);
    }
}
