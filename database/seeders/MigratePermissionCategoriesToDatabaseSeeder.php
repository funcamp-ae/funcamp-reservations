<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PermissionCategory;
use App\Models\Permission;

class MigratePermissionCategoriesToDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear permission cache
        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        // Define all categories from the hardcoded mappings
        $categories = [
            ['slug' => 'customers', 'name_ar' => 'العملاء', 'name_en' => 'Customers', 'sort_order' => 1],
            ['slug' => 'strategies', 'name_ar' => 'الاستراتيجيات', 'name_en' => 'Strategies', 'sort_order' => 2],
            ['slug' => 'inventory', 'name_ar' => 'المخزون', 'name_en' => 'Inventory', 'sort_order' => 3],
            ['slug' => 'addons', 'name_ar' => 'الإضافات', 'name_en' => 'Addons', 'sort_order' => 4],
            ['slug' => 'camp-types', 'name_ar' => 'نوع المخيم', 'name_en' => 'Camp Types', 'sort_order' => 5],
            ['slug' => 'bookings', 'name_ar' => 'الحجوزات', 'name_en' => 'Bookings', 'sort_order' => 6],
            ['slug' => 'orders', 'name_ar' => 'الطلبات', 'name_en' => 'Orders', 'sort_order' => 7],
            ['slug' => 'financial', 'name_ar' => 'النظام المالي', 'name_en' => 'Financial System', 'sort_order' => 8],
            ['slug' => 'expense-items', 'name_ar' => 'بنود المصاريف', 'name_en' => 'Expense Items', 'sort_order' => 9],
            ['slug' => 'expenses', 'name_ar' => 'المصاريف', 'name_en' => 'Expenses', 'sort_order' => 10],
            ['slug' => 'daily-reports', 'name_ar' => 'التقارير اليومية', 'name_en' => 'Daily Reports', 'sort_order' => 11],
            ['slug' => 'payments', 'name_ar' => 'المدفوعات', 'name_en' => 'Payments', 'sort_order' => 12],
            ['slug' => 'statistics', 'name_ar' => 'الإحصائيات', 'name_en' => 'Statistics', 'sort_order' => 13],
            ['slug' => 'meetings', 'name_ar' => 'الاجتماعات', 'name_en' => 'Meetings', 'sort_order' => 14],
            ['slug' => 'violations', 'name_ar' => 'المخالفات', 'name_en' => 'Violations', 'sort_order' => 15],
            ['slug' => 'scheduling', 'name_ar' => 'الجدولة', 'name_en' => 'Scheduling', 'sort_order' => 16],
            ['slug' => 'admins', 'name_ar' => 'الموظفين', 'name_en' => 'Employees', 'sort_order' => 17],
            ['slug' => 'roles', 'name_ar' => 'الصلاحيات', 'name_en' => 'Roles', 'sort_order' => 18],
            ['slug' => 'users', 'name_ar' => 'المستخدمين', 'name_en' => 'Users', 'sort_order' => 19],
            ['slug' => 'banners', 'name_ar' => 'البانرات', 'name_en' => 'Banners', 'sort_order' => 20],
            ['slug' => 'categories', 'name_ar' => 'الفئات', 'name_en' => 'Categories', 'sort_order' => 21],
            ['slug' => 'services', 'name_ar' => 'الخدمات', 'name_en' => 'Services', 'sort_order' => 22],
            ['slug' => 'stocks', 'name_ar' => 'المخزون', 'name_en' => 'Stocks', 'sort_order' => 23],
            ['slug' => 'surveys', 'name_ar' => 'الاستبيانات', 'name_en' => 'Surveys', 'sort_order' => 24],
            ['slug' => 'banks', 'name_ar' => 'البنوك', 'name_en' => 'Banks', 'sort_order' => 25],
            ['slug' => 'tasks', 'name_ar' => 'المهام', 'name_en' => 'Tasks', 'sort_order' => 26],
            ['slug' => 'equipment', 'name_ar' => 'قائمة موقع المعدات', 'name_en' => 'Equipment Location List', 'sort_order' => 27],
            ['slug' => 'camp-reports', 'name_ar' => 'تقارير حالة المخيمات', 'name_en' => 'Camps Status Reports', 'sort_order' => 28],
            ['slug' => 'terms-settings', 'name_ar' => 'إعدادات الشروط', 'name_en' => 'Terms Settings', 'sort_order' => 29],
            ['slug' => 'whatsapp-templates', 'name_ar' => 'نماذج واتساب', 'name_en'=> 'Whatsapp Templates', 'sort_order' => 30],
            ['slug' => 'manual-whatsapp-sends', 'name_ar' => 'ارسال واتساب يدوي', 'name_en' => 'Manual Whatsapp Sends', 'sort_order' => 31],
            ['slug' => 'general_payments', 'name_ar' => 'المدفوعات العامة', 'name_en' => 'General Payments', 'sort_order' => 32],
            ['slug' => 'bank-accounts', 'name_ar' => 'الحسابات البنكية', 'name_en' => 'Bank Accounts', 'sort_order' => 33],
            ['slug' => 'transactions', 'name_ar' => 'المعاملات', 'name_en' => 'Transactions', 'sort_order' => 34],
            ['slug' => 'pages', 'name_ar' => 'الصفحات', 'name_en' => 'Pages', 'sort_order' => 35],
            ['slug' => 'contact-guides', 'name_ar' => 'جهات الاتصال', 'name_en' => 'Contact Guides', 'sort_order' => 36],
            ['slug' => 'meeting-locations', 'name_ar' => 'مواقع الاجتماعات', 'name_en' => 'Meetings Locations', 'sort_order' => 37],
            ['slug' => 'violation-types', 'name_ar' => 'انواع المخالفات', 'name_en' => 'Violation Types', 'sort_order' => 38],
            ['slug' => 'financial-reports', 'name_ar' => 'التقارير المالية', 'name_en' => 'Financial reports', 'sort_order' => 39],
            ['slug' => 'attendance', 'name_ar' => 'نظام الحضور و الانصراف', 'name_en' => 'Attendance System', 'sort_order' => 40],
            ['slug' => 'permission-categories', 'name_ar' => 'فئات الصلاحيات', 'name_en' => 'Permission Categories', 'sort_order' => 41],
            ['slug' => 'permissions', 'name_ar' => 'الصلاحiات', 'name_en' => 'Permissions', 'sort_order' => 42],
        ];

        // Create categories
        $createdCategories = [];
        foreach ($categories as $categoryData) {
            $category = PermissionCategory::updateOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
            $createdCategories[$categoryData['slug']] = $category->id;
            $this->command->info("Created/Updated category: {$categoryData['name_en']}");
        }

        // Special mappings for permissions that don't follow prefix pattern
        $specialMappings = [
            'orders.quote' => 'bookings',
            'orders.invoice' => 'bookings',
            'orders.rate' => 'bookings',
            'orders.registeration-forms' => 'bookings',
            'orders.customers.check' => 'bookings',
            'order.verified' => 'bookings',
            'services.delete.internal' => 'camp-types',
            'questions.index' => 'surveys',
            'questions.create' => 'surveys',
            'questions.store' => 'surveys',
            'questions.edit' => 'surveys',
            'questions.update' => 'surveys',
            'questions.destroy' => 'surveys',
        ];

        // Get all permissions
        $allPermissions = Permission::all();
        $assignedCount = 0;

        foreach ($allPermissions as $permission) {
            $permissionName = $permission->name;
            $categorySlug = null;

            // Check special mappings first
            if (isset($specialMappings[$permissionName])) {
                $categorySlug = $specialMappings[$permissionName];
            } else {
                // Try to match by prefix (e.g., "customers.index" -> "customers")
                foreach (array_keys($createdCategories) as $slug) {
                    if (strpos($permissionName, $slug . '.') === 0) {
                        $categorySlug = $slug;
                        break;
                    }
                }
            }

            // Assign category if found
            if ($categorySlug && isset($createdCategories[$categorySlug])) {
                $permission->category_id = $createdCategories[$categorySlug];
                $permission->save();
                $assignedCount++;
            }
        }

        $this->command->info("Assigned {$assignedCount} permissions to categories.");
        $this->command->info("Migration completed successfully!");

        // Clear permission cache again
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
