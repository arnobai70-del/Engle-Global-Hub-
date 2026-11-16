<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the application's initial roles and permissions.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $guard = 'web';

        /*
        |--------------------------------------------------------------------------
        | Core Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [
            'dashboard.view',

            'users.view',
            'users.manage',

            'roles.view',
            'roles.manage',

            'settings.view',
            'settings.manage',

            'master-data.view',
            'master-data.manage',

            'agents.view',
            'agents.manage',

            'affiliates.view',
            'affiliates.manage',

            'students.view',
            'students.manage',

            'institutions.view',
            'institutions.manage',

            'student-enquiries.view',
            'student-enquiries.manage',

            'student-applications.view',
            'student-applications.manage',

            'student-documents.verify',

            'content.view',
            'content.manage',

            'service-enquiries.view',
            'service-enquiries.manage',

            'activity-logs.view',

            'integrations.view',

            'student-applications.view-all',

            'courses.view',
            'courses.manage',

            'appointments.view',
            'appointments.manage',

            'payments.view',
            'payments.record',
            'payments.verify',

            'seo.manage',

            'content.approve',
            'forums.moderate',
            'rewards.manage',
            'menus.manage',

            'reports.view',

            'system-logs.view',

            'flights.search',
            'flights.book',

            'hotels.search',
            'hotels.book',

            'tours.search',
            'tours.book',

            'visa.apply',
            'visa.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => $guard,
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => $guard,
        ]);

        $customer = Role::firstOrCreate([
            'name' => 'customer',
            'guard_name' => $guard,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Super Admin Permissions
        |--------------------------------------------------------------------------
        */

        $superAdmin->syncPermissions($permissions);

        /*
        |--------------------------------------------------------------------------
        | Admin Permissions
        |--------------------------------------------------------------------------
        */

        $admin->syncPermissions([
            'dashboard.view',

            'menus.manage',

            'users.view',
            'users.manage',

            'roles.view',

            'settings.view',

            'master-data.view',
            'master-data.manage',

            'agents.view',
            'agents.manage',

            'affiliates.view',
            'affiliates.manage',

            'students.view',
            'students.manage',

            'institutions.view',
            'institutions.manage',

            'student-enquiries.view',
            'student-enquiries.manage',

            'student-applications.view',
            'student-applications.manage',

            'student-documents.verify',

            'student-applications.view-all',
            'courses.view',
            'courses.manage',
            'appointments.view',
            'appointments.manage',
            'payments.view',
            'payments.record',
            'payments.verify',
            'seo.manage',
            'forums.moderate',
            'rewards.manage',

            'content.view',
            'content.manage',

            'service-enquiries.view',
            'service-enquiries.manage',

            'reports.view',

            'flights.search',
            'flights.book',

            'hotels.search',
            'hotels.book',

            'tours.search',
            'tours.book',

            'visa.apply',
            'visa.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Customer Permissions
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | Staff Roles (least privilege)
        |--------------------------------------------------------------------------
        | Consultants and visa staff see only applications assigned to them
        | (enforced by StudentApplicationPolicy); they lack "view-all".
        */

        $staffRoles = [
            'admission-consultant' => [
                'dashboard.view',
                'student-enquiries.view', 'student-enquiries.manage',
                'student-applications.view', 'student-applications.manage',
                'courses.view', 'appointments.view', 'appointments.manage',
                'payments.view', 'payments.record',
            ],
            'visa-officer' => [
                'dashboard.view',
                'student-applications.view', 'student-applications.manage',
                'student-documents.verify', 'appointments.view',
            ],
            'crm-staff' => [
                'dashboard.view',
                'student-enquiries.view', 'student-enquiries.manage',
                'service-enquiries.view', 'service-enquiries.manage',
                'appointments.view', 'appointments.manage', 'forums.moderate',
            ],
            'content-editor' => [
                'dashboard.view',
                'content.view', 'content.manage', 'master-data.view', 'forums.moderate',
            ],
            'seo-manager' => [
                'dashboard.view',
                'content.view', 'seo.manage',
            ],
        ];

        foreach ($staffRoles as $roleName => $rolePermissions) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard])
                ->syncPermissions($rolePermissions);
        }

        $customer->syncPermissions([
            'dashboard.view',
            'flights.search',
            'flights.book',
            'hotels.search',
            'hotels.book',
            'tours.search',
            'tours.book',
            'visa.apply',
            'visa.view',
        ]);

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
