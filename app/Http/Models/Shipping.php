<?php

namespace App\Http\Models;

use App\Http\Models\City;
use App\Http\Models\Order;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipping extends BaseModel
{
    use HasFactory;

    protected $table = 'shippings';
    protected $fillable = ['order_id', 'origin', 'destination', 'weight', 'courier', 'service', 'description', 'cost', 'etd', 'resi', 'status'];

    public function orders()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function city_origin()
    {
        return $this->belongsTo(City::class, 'origin');
    }

    public function city_destination()
    {
        return $this->belongsTo(City::class, 'destination');
    }

    public function getWeightAttribute($value)
    {
        return (int) $value;
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id', 
                    'order_id', 
                    'origin', 
                    'destination', 
                    'weight', 
                    'courier', 
                    'service', 
                    'description', 
                    'cost', 
                    'etd',
                    'resi',
                    'status',
                ]);
    }
}
