<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentLocationExport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'filters',
        'rows',
        'status',
        'pdf_path',
        'error_message',
        'locale',
        'prepared_at',
        'started_at',
        'finished_at',
        'expires_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'rows' => 'array',
        'prepared_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
