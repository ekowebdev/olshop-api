<?php

namespace App\Http\Models;

use App\Http\Models\City;
use App\Http\Models\User;
use Illuminate\Support\Str;
use App\Http\Models\ItemGift;
use App\Http\Models\Province;
use App\Http\Models\BaseModel;
use App\Http\Models\Subdistrict;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends BaseModel
{
    use HasFactory;

    protected $table = 'addresses';
    protected $fillable = ['user_id', 'province_id', 'city_id', 'subdistrict_id', 'postal_code', 'address'];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }

    public function subdistrict()
    {
        return $this->belongsTo(Subdistrict::class, 'subdistrict_id', 'subdistrict_id');
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id', 
                    'user_id', 
                    'province_id', 
                    'city_id',
                    'subdistrict_id', 
                    'postal_code', 
                    'address'
                ]);
    }
}

