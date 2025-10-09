<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CampReportItemMedia extends Model
{
    // الجدول الافتراضي سيكون camp_report_item_media — لا حاجة لتحديده يدويًا

    protected $fillable = [
        'camp_report_item_id',
        'type',   // photo | video | audio
        'path',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(CampReportItem::class, 'camp_report_item_id');
    }

    /* اختياري: رابط الملف على قرص hetzner */
    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::disk('hetzner')->url($this->path) : null;
    }

    /* سكوبات مفيدة (اختيارية) */
    public function scopePhotos($q) { return $q->where('type', 'photo'); }
    public function scopeVideos($q) { return $q->where('type', 'video'); }
    public function scopeAudios($q) { return $q->where('type', 'audio'); }
}
