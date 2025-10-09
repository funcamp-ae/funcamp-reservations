<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DailyReportMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_report_id',
        'type',
        'path',
    ];

    // باش يزيد حقل جديد تلقائي في JSON والـ Blade
    protected $appends = ['url'];

    public function report()
    {
        return $this->belongsTo(DailyReport::class, 'daily_report_id');
    }

    /**
     * ✅ Accessor: يرجّع رابط كامل للملف
     */
    public function getUrlAttribute()
    {
        if (!$this->path) {
            return null;
        }

        // لو البوكت public
        return Storage::disk('hetzner')->url($this->path);

        // 🔐 لو البوكت private وتحتاج روابط موقعّة:
        // return Storage::disk('hetzner')->temporaryUrl($this->path, now()->addMinutes(10));
    }
}
