<?php

namespace App\Http\Models;

use App\Http\Models\City;
use App\Http\Models\Redeem;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipping extends BaseModel
{
    use HasFactory;

    protected $table = 'shippings';
    protected $fillable = ['redeem_id', 'origin', 'destination', 'weight', 'courier', 'service', 'description', 'cost', 'etd'];

    public function redeems()
    {
        return $this->belongsTo(Redeem::class, 'redeem_id');
    }

    public function city_origin()
    {
        return $this->belongsTo(City::class, 'origin', 'city_id');
    }

    public function city_destination()
    {
        return $this->belongsTo(City::class, 'destination', 'city_id');
    }

    public function getWeightAttribute($value)
    {
        return (int) $value;
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id', 
                    'redeem_id', 
                    'origin', 
                    'destination', 
                    'weight', 
                    'courier', 
                    'service', 
                    'description', 
                    'cost', 
                    'etd',
                ]);
    }
}
