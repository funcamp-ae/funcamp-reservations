<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampReport extends Model
{
    protected $fillable = [
        'report_date',
        'service_id',
        'camp_name',
        'report_type',
        'custom_report_type',
        'created_by',
        'general_notes'
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CampReportItem::class);
    }
}
