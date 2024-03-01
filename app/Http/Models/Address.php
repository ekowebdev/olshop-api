<?php

namespace App\Http\Models;

use App\Http\Models\City;
use App\Http\Models\User;
use App\Http\Models\Order;
use App\Http\Models\Province;
use App\Http\Models\BaseModel;
use App\Http\Models\Subdistrict;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends BaseModel
{
    use HasFactory;

    protected $table = 'addresses';
    protected $fillable = ['user_id', 'person_name', 'person_phone', 'province_id', 'city_id', 'subdistrict_id', 'postal_code', 'street'];

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

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'addresses.id', 
                    'addresses.user_id', 
                    'addresses.person_name', 
                    'addresses.person_phone',
                    'addresses.province_id', 
                    'addresses.city_id',
                    'addresses.subdistrict_id', 
                    'addresses.postal_code', 
                    'addresses.street',
                    DB::raw('
                        (
                            CASE WHEN users.main_address_id = addresses.id 
                                THEN 1 
                                ELSE 0 
                            END
                        ) AS is_main
                    '),
                ])
                ->leftJoin('users', 'users.main_address_id', '=', 'addresses.id');
    }
}

