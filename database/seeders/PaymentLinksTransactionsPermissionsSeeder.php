<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PaymentLinksTransactionsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear Spatie cache before changes
        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        // Define the two permissions with nicknames and group as requested
        $perms = [
            ['name' => 'financial.payment-links.transactions.index', 'nickname_ar' => 'عرض معاملات روابط الدفع', 'nickname_en' => 'View Payment Link Transactions', 'group' => 'financial'],
            ['name' => 'financial.payment-links.transactions.refund', 'nickname_ar' => 'استرجاع عملية روابط الدفع', 'nickname_en' => 'Refund Payment Link Transaction', 'group' => 'financial'],
        ];

        foreach ($perms as $p) {
            Permission::updateOrCreate(
                ['name' => $p['name'], 'guard_name' => 'web'],
                [
                    'nickname_ar' => $p['nickname_ar'],
                    'nickname_en' => $p['nickname_en'],
                    'guard_name' => 'web',
                ]
            );
        }

        // Assign these permissions to the super-admin role
        $superAdmin = Role::where('name', 'super-admin')->first();
        if ($superAdmin) {
            foreach ($perms as $p) {
                $superAdmin->givePermissionTo($p['name']);
            }
        }

        // Assigning to the role is sufficient; no need to touch individual users

        // Clear cache after changes
        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }
}
