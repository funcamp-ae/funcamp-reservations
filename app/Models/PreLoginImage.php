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
    protected $appends = ['image_url'];

    /**
     * Setter: نخزن الصورة في مجلد فيه رقم الأوردر
     */
    public function setImageAttribute($value)
    {
        if ($value instanceof UploadedFile) {
            $orderId = $this->order_id ?? 'unknown';
            $path = "uploads/signin/photos/order_{$orderId}";
            $this->attributes['image'] = $value->store($path, 'hetzner');
        } else {
            $this->attributes['image'] = $value;
        }
    }

    /**
     * Getter: يرجع رابط العرض
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::disk('hetzner')->url($this->image) : null;
    }

    /**
     * العلاقة مع Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
