<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'nickname_ar',
        'nickname_en',
        'category_id'
    ];

    protected $casts = [
        'category_id' => 'integer'
    ];

    /**
     * Get the category this permission belongs to
     */
    public function category()
    {
        return $this->belongsTo(PermissionCategory::class, 'category_id');
    }

    /**
     * Get localized nickname
     */
    public function getLocalizedNicknameAttribute()
    {
        return app()->getLocale() == 'ar' ? $this->nickname_ar : $this->nickname_en;
    }
}
