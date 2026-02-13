<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\IPermissionCategoryRepository;
use App\Requests\dashboard\CreateUpdatePermissionCategoryRequest;
use Illuminate\Support\Str;

class PermissionCategoryController extends Controller
{
    private $permissionCategoryRepository;

    public function __construct(IPermissionCategoryRepository $permissionCategoryRepository)
    {
        $this->permissionCategoryRepository = $permissionCategoryRepository;
    }

    public function index()
    {
        $categories = $this->permissionCategoryRepository->getAll(['column' => 'sort_order', 'dir' => 'ASC']);
        return view('dashboard.permission-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('dashboard.permission-categories.create');
    }

    public function store(CreateUpdatePermissionCategoryRequest $request)
    {
        $data = $request->validated();
        
        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($request->name_en);
        }

        $this->permissionCategoryRepository->create($data);

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['message' => __('dashboard.created_successfully')]);
    }

    public function edit($id)
    {
        $category = $this->permissionCategoryRepository->findOne($id);
        return view('dashboard.permission-categories.edit', compact('category'));
    }

    public function update(CreateUpdatePermissionCategoryRequest $request, $id)
    {
        $data = $request->validated();

        $this->permissionCategoryRepository->update($data, $id);

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['message' => __('dashboard.updated_successfully')]);
    }

    public function destroy($id)
    {
        $this->permissionCategoryRepository->forceDelete($id);

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

        if ($this->permissionCategoryRepository->deleteForceWhereIn('id', $ids)) {
            // Clear permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return response()->json(['message' => __('dashboard.deleted_successfully')]);
        } else {
            return response()->json(['message' => __('dashboard.delete_failed')], 500);
        }
    }

    public function toggleStatus($id)
    {
        $category = $this->permissionCategoryRepository->findOne($id);
        $this->permissionCategoryRepository->update([
            'is_active' => !$category->is_active
        ], $id);

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['message' => __('dashboard.updated_successfully')]);
    }
}
