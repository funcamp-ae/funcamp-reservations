<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_addon')
            ->using(OrderAddon::class)
            ->withPivot('verified', 'count', 'price', 'handled_by', 'description', 'id', 'account_id', 'payment_method' , 'statement')
            ->withTimestamps();
    }

}
