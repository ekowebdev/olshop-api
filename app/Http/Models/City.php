<?php

namespace App\Http\Models;

use Illuminate\Support\Str;
use App\Http\Models\Address;
use App\Http\Models\Province;
use App\Http\Models\Shipping;
use App\Http\Models\BaseModel;
use App\Http\Models\Subdistrict;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends BaseModel
{
    use HasFactory;

    protected $table = 'cities';
    protected $fillable = ['province_id', 'name', 'postal_code'];

    public function address()
    {
        return $this->hasMany(Address::class);
    }

    public function shippings()
    {
        return $this->hasMany(Shipping::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }

    public function subdistricts()
    {
        return $this->hasMany(Subdistrict::class);
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'city_id', 
                    'province_id', 
                    'name', 
                    'postal_code',
                ]);
    }
}

