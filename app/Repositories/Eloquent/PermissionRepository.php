<?php
namespace App\Repositories\Eloquent;

use App\Repositories\IPermissionRepository;
use App\Models\Permission;


class PermissionRepository extends BaseRepository implements IPermissionRepository
{
    public function __construct()
    {
        $this->model = new Permission();
    }

}