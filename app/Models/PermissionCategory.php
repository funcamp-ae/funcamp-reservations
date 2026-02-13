<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionCategory extends Model
{
    protected $fillable = [
        'slug',
        'name_ar',
        'name_en',
        'icon',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Default ordering
     */
    protected static function boot()
    {
        parent::boot();
        
        static::addGlobalScope('order', function ($builder) {
            $builder->orderBy('sort_order', 'asc')->orderBy('name_en', 'asc');
        });
    }

    /**
     * Get permissions belonging to this category
     */
    public function permissions()
    {
        return $this->hasMany(\App\Models\Permission::class, 'category_id');
    }

    /**
     * Scope to get only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get localized name
     */
    public function getLocalizedNameAttribute()
    {
        return app()->getLocale() == 'ar' ? $this->name_ar : $this->name_en;
    }
}
