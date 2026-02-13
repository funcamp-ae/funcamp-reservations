<?php

namespace App\Repositories\Eloquent;

use App\Repositories\IPermissionCategoryRepository;
use App\Models\PermissionCategory;

class PermissionCategoryRepository extends BaseRepository implements IPermissionCategoryRepository
{
    public function __construct()
    {
        $this->model = new PermissionCategory();
    }
}
