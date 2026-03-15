<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampReportSummaryExport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'filters',
        'rows',
        'locale',
        'prepared_at',
        'expires_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'rows' => 'array',
        'prepared_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
