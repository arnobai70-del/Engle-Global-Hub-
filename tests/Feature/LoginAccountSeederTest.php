<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\LoginAccountSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_and_admin_can_sign_in_with_the_starter_passwords(): void
    {
        $this->seed([RolePermissionSeeder::class, LoginAccountSeeder::class]);

        $this->assertTrue(User::query()->where('email', 'superadmin@eagleglobalhub.test')->sole()->hasRole('super-admin'));
        $this->assertTrue(User::query()->where('email', 'admin@eagleglobalhub.test')->sole()->hasRole('admin'));

        $this->post('/login', ['email' => 'superadmin@eagleglobalhub.test', 'password' => 'superadmin']);
        $this->assertAuthenticated();

        $this->post('/logout');
        $this->post('/login', ['email' => 'admin@eagleglobalhub.test', 'password' => 'admin']);
        $this->assertAuthenticated();
    }

    public function test_reseeding_keeps_a_changed_password_and_adds_no_duplicates(): void
    {
        $this->seed([RolePermissionSeeder::class, LoginAccountSeeder::class]);
        User::query()->where('email', 'admin@eagleglobalhub.test')->update(['password' => Hash::make('changed-password')]);

        $this->seed(LoginAccountSeeder::class);

        $admins = User::query()->whereIn('email', ['superadmin@eagleglobalhub.test', 'admin@eagleglobalhub.test'])->get();
        $this->assertCount(2, $admins);
        $this->assertTrue(Hash::check('changed-password', $admins->firstWhere('email', 'admin@eagleglobalhub.test')->password));
    }
}
