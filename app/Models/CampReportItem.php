<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampReportItem extends Model
{
    protected $fillable = [
        'camp_report_id',
        'item_name',
        'notes',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(CampReport::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(CampReportItemMedia::class, 'camp_report_item_id');
    }

    public function photos(): HasMany  { return $this->media()->where('type', 'photo'); }
public function audios(): HasMany  { return $this->media()->where('type', 'audio'); }
public function videos(): HasMany  { return $this->media()->where('type', 'video'); }

}
