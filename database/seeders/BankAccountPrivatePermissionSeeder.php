<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class BankAccountPrivatePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create the bank-accounts.private.show permission
        $permission = Permission::updateOrCreate(
            ['name' => 'bank-accounts.private.show', 'guard_name' => 'web'],
            [
                'nickname_ar' => 'عرض الحسابات البنكية الخاصة',
                'nickname_en' => 'View Private Bank Accounts',
                'guard_name' => 'web'
            ]
        );

        $this->command->info('Permission "bank-accounts.private.show" created successfully.');

        // Get super-admin role and give it the permission
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permission);
            $this->command->info('Permission assigned to super-admin role.');
        } else {
            $this->command->warn('Super-admin role not found.');
        }

        // Get admin@funcamp.ae user and give the permission directly
        $admin = User::where('email', 'admin@funcamp.ae')->first();
        if ($admin) {
            $admin->givePermissionTo($permission);
            $this->command->info('Permission assigned to admin@funcamp.ae user.');
        } else {
            $this->command->warn('User admin@funcamp.ae not found.');
        }

        $this->command->info('✅ Bank account private permission setup completed!');
    }
}
