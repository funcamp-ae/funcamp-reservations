<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class PreLogoutAudio extends Model
{
    protected $table = 'pre_logout_audios'; // جدول خاص بالخروج
    protected $guarded = [];
    protected $appends = ['audio_url'];

    /**
     * Setter: نخزن الملف في مجلد فيه رقم الأوردر
     */
    public function setAudioAttribute($value)
    {
        if ($value instanceof UploadedFile) {
            // رقم الأوردر المرتبط
            $orderId = $this->order_id ?? 'unknown';

            // مسار التخزين فيه orderId
            $path = "uploads/signout/audios/order_{$orderId}";

            $this->attributes['audio'] = $value->store($path, 'hetzner');
        } else {
            $this->attributes['audio'] = $value;
        }
    }

    /**
     * Getter: يرجع الرابط المباشر للملف
     */
    public function getAudioUrlAttribute(): ?string
    {
        return $this->audio ? Storage::disk('hetzner')->url($this->audio) : null;
    }

    /**
     * العلاقة مع Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
