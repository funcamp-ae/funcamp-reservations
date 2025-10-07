<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PreLoginVideo extends Model
{
    protected $guarded = [];
    protected $appends = ['video_url'];

    /**
     * Setter: تخزين الفيديو مع رقم الأوردر في المسار
     */
    public function setVideoAttribute($value)
    {
        if ($value instanceof UploadedFile) {
            $orderId = $this->order_id ?? 'unknown';
            $path = "uploads/signin/videos/order_{$orderId}";
            $this->attributes['video'] = $value->store($path, 'hetzner');
        } elseif (is_string($value)) {
            // نخزن المفتاح مباشرة إذا جايني string من FilePond أو API
            $this->attributes['video'] = $value;
        }
    }

    /**
     * Getter: يرجع الرابط المباشر للفيديو
     */
    public function getVideoUrlAttribute()
    {
        return $this->video ? Storage::disk('hetzner')->url($this->video) : null;
    }

    /**
     * العلاقة مع Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
