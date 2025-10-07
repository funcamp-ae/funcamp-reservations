<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PreLogoutVideo extends Model
{
    protected $table = 'pre_logout_videos'; // جدول خاص بفيديوهات تسجيل الخروج
    protected $guarded = [];
    protected $appends = ['video_url'];

    /**
     * Setter: نخزن الفيديو في التخزين إذا كان UploadedFile
     */
    public function setVideoAttribute($value)
    {
        if ($value instanceof UploadedFile) {
            // جيب رقم الأوردر المرتبط
            $orderId = $this->order_id ?? 'unknown';

            // مسار التخزين مع رقم الأوردر
            $path = "uploads/signout/videos/order_{$orderId}";

            $this->attributes['video'] = $value->store($path, 'hetzner');
        } elseif (is_string($value)) {
            $this->attributes['video'] = $value; // نخزن الـ key مباشرة
        }
    }

    /**
     * Getter: يرجع الرابط المباشر للفيديو
     */
    public function getVideoUrlAttribute(): ?string
    {
        return $this->video ? Storage::disk('hetzner')->url($this->video) : null;
    }

    /**
     * علاقة مع Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
