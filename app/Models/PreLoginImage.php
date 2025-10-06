<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PreLoginImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    // نضيف URL كمُلحَق (accessor) من غير ما نفسد قيمة image الخام
    protected $appends = ['image_url'];

    // خزّن المسار الخام على ديسك hetzner
    public function setImageAttribute($value)
    {
        if ($value instanceof UploadedFile) {
            $this->attributes['image'] = $value->store('uploads/signin/photos', 'hetzner');
        }
    }

    // عطيني URL للعرض
    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::disk('hetzner')->url($this->image) : null;
    }
}
