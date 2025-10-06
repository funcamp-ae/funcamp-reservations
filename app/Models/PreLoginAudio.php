<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class PreLoginAudio extends Model
{

        protected $table = 'pre_login_audios'; // <<< هنا نزيدو

    protected $guarded = []; // يسمح بتعبئة كل الأعمدة
    protected $appends = ['audio_url'];

    /**
     * Setter: نخزن الملف في التخزين إذا كان UploadedFile
     */
    public function setAudioAttribute($value)
    {
        if ($value instanceof UploadedFile) {
            $this->attributes['audio'] = $value->store('uploads/signin/audios', 'hetzner');
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
     * علاقة مع Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
