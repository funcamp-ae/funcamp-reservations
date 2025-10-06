<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PreLoginVideo extends Model
{
    protected $guarded = [];
    protected $appends = ['video_url'];

    public function setVideoAttribute($value) {
    if ($value instanceof UploadedFile) {
        $this->attributes['video'] = $value->store('uploads/signin/videos', 'hetzner');
    } elseif (is_string($value)) {
        $this->attributes['video'] = $value; // نخزّن الـ key مباشرة
    }
}


    public function getVideoUrlAttribute() {
        return $this->video ? Storage::disk('hetzner')->url($this->video) : null;
    }
}
