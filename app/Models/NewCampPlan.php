<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewCampPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'new_camp_name',
        'items',
        'created_by',
    ];

    protected $casts = [
        'items' => 'array',
    ];
}
