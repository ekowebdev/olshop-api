<?php

namespace App\Http\Models;

use App\Http\Models\City;
use App\Http\Models\User;
use App\Http\Models\Redeem;
use Illuminate\Support\Str;
use App\Http\Models\ItemGift;
use App\Http\Models\Province;
use App\Http\Models\BaseModel;
use App\Http\Models\Subdistrict;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends BaseModel
{
    use HasFactory;

    protected $table = 'addresses';
    protected $fillable = ['user_id', 'province_id', 'city_id', 'subdistrict_id', 'postal_code', 'address', 'is_main'];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($address) {
            if ($address->is_main === 'yes' && $address->user_id) {
                $existing_main_address = $address->users->address()
                    ->where('is_main', 'yes')
                    ->where('id', '<>', $address->id)
                    ->first();

                if ($existing_main_address) {
                    throw new ValidationException(json_encode(['user_id' => [trans('error.main_address_exists', ['id' => $address->user_id])]]));
                }
            }
        });
    }

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

    public function redeems()
    {
        return $this->hasMany(Redeem::class);
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
                    'address',
                    'is_main',
                ]);
    }
}

