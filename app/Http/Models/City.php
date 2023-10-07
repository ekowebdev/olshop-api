<?php

namespace App\Http\Models;

use App\Http\Models\Address;
use Illuminate\Support\Str;
use App\Http\Models\Shipping;
use App\Http\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends BaseModel
{
    use HasFactory;

    protected $table = 'cities';
    protected $fillable = ['province_id', 'city_name', 'postal_code'];

    public function address()
    {
        return $this->hasMany(Address::class);
    }

    public function shippings()
    {
        return $this->hasMany(Shipping::class);
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'city_id', 
                    'province_id', 
                    'city_name', 
                    'postal_code',
                ]);
    }
}

