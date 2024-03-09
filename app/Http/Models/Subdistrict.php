<?php

namespace App\Http\Models;

use App\Http\Models\City;
use App\Http\Models\Address;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subdistrict extends BaseModel
{
    use HasFactory;

    protected $table = 'subdistricts';
    protected $fillable = ['city_id', 'name'];

    public function address()
    {
        return $this->hasMany(Address::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id',
                    'city_id',  
                    'name',
                ]);
    }
}

