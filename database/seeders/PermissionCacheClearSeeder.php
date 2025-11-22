<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class PermissionCacheClearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear permission cache using Spatie's permission package
        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            $role = Role::where('name', 'super-admin')->first()->givePermissionTo(Permission::all());
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
              // Assign all permissions to super-admin role

        // User::role('super-admin')->get()->assignRole('super-admin');

            $this->command->info('Permission cache cleared using Spatie PermissionRegistrar.');
        }

        // Clear general cache
        Cache::flush();
        $this->command->info('General cache cleared.');

        // Clear config cache
        Artisan::call('config:clear');
        $this->command->info('Config cache cleared.');

        // Clear route cache
        Artisan::call('route:clear');
        $this->command->info('Route cache cleared.');

        // Clear view cache
        Artisan::call('view:clear');
        $this->command->info('View cache cleared.');

        $this->command->info('✅ All permission and application caches have been cleared successfully!');
    }
}
