<?php

namespace App\Http\Models;

use App\Http\Models\City;
use Illuminate\Support\Str;
use App\Http\Models\Address;
use App\Http\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subdistrict extends BaseModel
{
    use HasFactory;

    protected $table = 'subdistricts';
    protected $fillable = ['city_id', 'subdistrict_name'];

    public function address()
    {
        return $this->hasMany(Address::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'subdistrict_id',
                    'city_id',  
                    'subdistrict_name',
                ]);
    }
}

