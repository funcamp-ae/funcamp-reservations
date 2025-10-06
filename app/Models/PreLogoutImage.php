<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PreLogoutImage extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['image_url'];

    public function setImageAttribute($value)
    {
        if ($value instanceof UploadedFile) {
            $this->attributes['image'] = $value->store('uploads/signout/images', 'hetzner');
        }
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::disk('hetzner')->url($this->image) : null;
    }
}
