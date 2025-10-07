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
            // جيب رقم الأوردر
            $orderId = $this->order_id ?? 'unknown';

            // مسار التخزين فيه رقم الأوردر
            $path = "uploads/signout/images/order_{$orderId}";

            $this->attributes['image'] = $value->store($path, 'hetzner');
        } else {
            $this->attributes['image'] = $value;
        }
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::disk('hetzner')->url($this->image) : null;
    }
}
