<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\IPermissionRepository;
use App\Repositories\IPermissionCategoryRepository;
use App\Requests\dashboard\CreateUpdatePermissionRequest;
use App\Models\Permission;

class PermissionController extends Controller
{
    private $permissionRepository;
    private $permissionCategoryRepository;

    public function __construct(
        IPermissionRepository $permissionRepository,
        IPermissionCategoryRepository $permissionCategoryRepository
    ) {
        $this->permissionRepository = $permissionRepository;
        $this->permissionCategoryRepository = $permissionCategoryRepository;
    }

    public function index()
    {
        $permissions = $this->permissionRepository->getAll(['column' => 'name', 'dir' => 'ASC']);
        return view('dashboard.permissions.index', compact('permissions'));
    }

    public function create()
    {
        $categories = $this->permissionCategoryRepository->getAll(['column' => 'sort_order', 'dir' => 'ASC']);
        return view('dashboard.permissions.create', compact('categories'));
    }

    public function store(CreateUpdatePermissionRequest $request)
    {
        $data = $request->validated();
        
        // Ensure guard_name is set
        if (!isset($data['guard_name'])) {
            $data['guard_name'] = 'web';
        }

        // Check if permission already exists
        if (Permission::where('name', $data['name'])->exists()) {
            return response()->json(['message' => __('dashboard.permission_already_exists')], 409);
        }

        $this->permissionRepository->create($data);

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['message' => __('dashboard.created_successfully')]);
    }

    public function edit($id)
    {
        $permission = $this->permissionRepository->findOne($id);
        $categories = $this->permissionCategoryRepository->getAll(['column' => 'sort_order', 'dir' => 'ASC']);
        return view('dashboard.permissions.edit', compact('permission', 'categories'));
    }

    public function update(CreateUpdatePermissionRequest $request, $id)
    {
        $data = $request->validated();

        // Permission name cannot be changed as it's used in code authorization checks
        // Only allow updating display names (nickname_ar, nickname_en) and category

        $this->permissionRepository->update($data, $id);

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['message' => __('dashboard.updated_successfully')]);
    }

    public function destroy($id)
    {
        $this->permissionRepository->forceDelete($id);

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['message' => __('dashboard.deleted_successfully')]);
    }

    public function deleteAll(Request $request)
    {
        $requestIds = json_decode($request->data);
        $ids = [];

        foreach ($requestIds as $id) {
            $ids[] = $id->id;
        }

        if ($this->permissionRepository->deleteForceWhereIn('id', $ids)) {
            // Clear permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return response()->json(['message' => __('dashboard.deleted_successfully')]);
        } else {
            return response()->json(['message' => __('dashboard.delete_failed')], 500);
        }
    }
}
