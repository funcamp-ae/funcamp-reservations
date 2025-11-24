<?php

namespace App\Models;

use App\Models\Order;
use App\Models\Stock;
use App\Models\ServiceReport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function stocks()
    {
        return $this->belongsToMany(Stock::class, 'service_stock')
                ->withPivot([
                    'count', 
                    'placed_qty', 
                    'is_completed', 
                    'not_completed_reason', 
                    'required_qty',
                    'id',
                    'latest_activity',
                    'is_from_old_reservation',
                    'discount_from_old_reservation',
                    'discount_from_inventory',
                    'discount_note'
                ]);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_service');
    }
    /**
     * Return services that are NOT booked on the provided date.
     * Usage: Service::availableOn('2025-11-22')
     *
     * @param  string|\Carbon\Carbon  $day
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function availableOn($day)
    {
        return Service::whereDoesntHave('orders', function($q) use ($day) {
            $q->whereDate('date', $day);
        })->orderBy('created_at' , 'desc')->get();
    }
    public function reports()
    {
        return $this->hasMany(ServiceReport::class);
    }

    public function registrationforms()
    {
        return $this->hasMany(Registrationform::class, 'service_id');
    }


}
