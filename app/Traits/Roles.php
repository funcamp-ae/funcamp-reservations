<?php

namespace App\Traits;

use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use App\Models\Permission;
use App\Models\PermissionCategory;

trait Roles
{
    function addRole()
    {
        return $this->generatePermissionHTML();
    }

    private function generatePermissionHTML($selectedPermissions = [])
    {
        // Get all active categories with their permissions from database
        $categories = PermissionCategory::with(['permissions' => function($query) {
            $query->orderBy('name', 'asc');
        }])->active()->orderBy('sort_order', 'asc')->get();

        // Handle uncategorized permissions (permissions without category_id)
        $uncategorizedPermissions = Permission::whereNull('category_id')->orderBy('name', 'asc')->get();

        $html = '';
        $id = 0;

       // Render categorized permissions
        foreach ($categories as $category) {
            if ($category->permissions->isEmpty()) {
                continue; // Skip empty categories
            }

            $parent_class = 'group_' . $category->slug . '_' . $id++;
            // Use current app locale for category title
            $groupTitle = app()->getLocale() == 'ar' ? $category->name_ar : $category->name_en;

            $html .= '
                    <div class="col-md-4">
                        <div class="card permissionCard package bg-white shadow">
                            <div class="role-title text-white" style="display: flex; justify-content: space-between;">
                                <div style="display: flex; flex-direction: row; align-items: center">
                                    <div class="icheck-primary d-inline">
                                    <input type="checkbox" class="group-parent" data-group="' . $parent_class . '" id="' . $parent_class . '_parent">
                                    <label for="' . $parent_class . '_parent" dir="ltr"></label>
                                    </div>
                                <p class="text-white selectP">' . htmlspecialchars($groupTitle) . '</p>
                                </div>
                                <div style="display: flex; flex-direction: row-reverse; align-items: center">
                                    <p class="text-white selectP">' . __("dashboard.select_all") . '</p>
                                    <input type="checkbox" class="checkChilds checkChilds_' . $parent_class . '" data-parent="' . $parent_class . '">
                                </div>
                        </div>
                        <div class="p-2" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                            <input type="text"
                                class="form-control form-control-sm permission-search"
                                data-group="' . $parent_class . '"
                                placeholder="' . __("dashboard.search_permissions") . '..."
                                style="border: 1px solid #dee2e6;">
                        </div>
                        <div class="card permissionCard bg-white shadow">
                            <ul class="list-unstyled permission-list" data-group="' . $parent_class . '">';

            foreach ($category->permissions as $permission) {
                $permissionName = $permission->name;
                $isSelected = in_array($permissionName, $selectedPermissions) ? 'checked' : '';
                // Use current app locale for permission title
                $title = app()->getLocale() == 'ar' ? 
                    (!empty($permission->nickname_ar) ? $permission->nickname_ar : $permissionName) : 
                    (!empty($permission->nickname_en) ? $permission->nickname_en : $permissionName);

                $html .= '
                            <li class="permission-item" data-permission-text="' . strtolower(htmlspecialchars($title)) . '" data-permission-name="' . strtolower($permissionName) . '">
                                <div class="form-group clearfix">
                                    <div class="icheck-primary d-inline">
                                <input type="checkbox" name="permissions[]" data-parent="' . $parent_class . '" value="' . htmlspecialchars($permissionName) . '" id="' . htmlspecialchars($permissionName) . '" class="childs ' . $parent_class . '" ' . $isSelected . '>
                                <label for="' . htmlspecialchars($permissionName) . '" dir="ltr"></label>
                                    </div>
                            <label for="' . htmlspecialchars($permissionName) . '">' . htmlspecialchars($title) . '</label>
                                </div>
                            </li>';
            }

            $html .= '
                            </ul>
                        </div>
                    </div>
                </div>';
        }

        // Render uncategorized permissions in "Other" category if any exist
        if ($uncategorizedPermissions->isNotEmpty()) {
            $parent_class = 'group_other_' . $id++;
            $groupTitle = app()->getLocale() == 'ar' ? 'أخرى' : 'Other';

            $html .= '
                    <div class="col-md-4">
                        <div class="card permissionCard package bg-white shadow">
                            <div class="role-title text-white" style="display: flex; justify-content: space-between;">
                                <div style="display: flex; flex-direction: row; align-items: center">
                                    <div class="icheck-primary d-inline">
                                    <input type="checkbox" class="group-parent" data-group="' . $parent_class . '" id="' . $parent_class . '_parent">
                                    <label for="' . $parent_class . '_parent" dir="ltr"></label>
                                    </div>
                                <p class="text-white selectP">' . $groupTitle . '</p>
                                </div>
                                <div style="display: flex; flex-direction: row-reverse; align-items: center">
                                    <p class="text-white selectP">' . __("dashboard.select_all") . '</p>
                                    <input type="checkbox" class="checkChilds checkChilds_' . $parent_class . '" data-parent="' . $parent_class . '">
                                </div>
                        </div>
                        <div class="p-2" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                            <input type="text"
                                class="form-control form-control-sm permission-search"
                                data-group="' . $parent_class . '"
                                placeholder="' . __("dashboard.search_permissions") . '..."
                                style="border: 1px solid #dee2e6;">
                        </div>
                        <div class="card permissionCard bg-white shadow">
                            <ul class="list-unstyled permission-list" data-group="' . $parent_class . '">';

            foreach ($uncategorizedPermissions as $permission) {
                $permissionName = $permission->name;
                $isSelected = in_array($permissionName, $selectedPermissions) ? 'checked' : '';
                $title = app()->getLocale() == 'ar' ? 
                    (!empty($permission->nickname_ar) ? $permission->nickname_ar : $permissionName) : 
                    (!empty($permission->nickname_en) ? $permission->nickname_en : $permissionName);

                $html .= '
                            <li class="permission-item" data-permission-text="' . strtolower(htmlspecialchars($title)) . '" data-permission-name="' . strtolower($permissionName) . '">
                                <div class="form-group clearfix">
                                    <div class="icheck-primary d-inline">
                                <input type="checkbox" name="permissions[]" data-parent="' . $parent_class . '" value="' . htmlspecialchars($permissionName) . '" id="' . htmlspecialchars($permissionName) . '" class="childs ' . $parent_class . '" ' . $isSelected . '>
                                <label for="' . htmlspecialchars($permissionName) . '" dir="ltr"></label>
                                    </div>
                            <label for="' . htmlspecialchars($permissionName) . '">' . htmlspecialchars($title) . '</label>
                                </div>
                            </li>';
            }

            $html .= '
                            </ul>
                        </div>
                    </div>
                </div>';
        }

        return $html;
    }

    // editRole
    function editRole($id)
    {
        $role = Role::with('permissions')->find($id);
        $selectedPermissions = $role->permissions()->pluck('name')->toArray();

        return $this->generatePermissionHTML($selectedPermissions);
    }
}
