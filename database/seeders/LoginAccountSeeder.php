<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates the starter staff logins (one per admin role):
 * - superadmin@eagleglobalhub.test / superadmin (super-admin)
 * - admin@eagleglobalhub.test / admin (admin)
 *
 * An account is only created when its email is missing, so a password changed
 * later is never reset back to the starter one. Change both passwords after
 * the first sign-in on any public server.
 */
class LoginAccountSeeder extends Seeder
{
    /** @var list<array{name: string, email: string, password: string, role: string}> */
    private const ACCOUNTS = [
        ['name' => 'Super Admin', 'email' => 'superadmin@eagleglobalhub.test', 'password' => 'superadmin', 'role' => 'super-admin'],
        ['name' => 'Admin', 'email' => 'admin@eagleglobalhub.test', 'password' => 'admin', 'role' => 'admin'],
    ];

    public function run(): void
    {
        foreach (self::ACCOUNTS as $account) {
            $user = User::query()->firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($account['password']),
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasRole($account['role'])) {
                $user->assignRole($account['role']);
            }
        }
    }
}
