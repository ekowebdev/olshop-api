<?php

namespace App\Http\Models;

use App\Http\Models\User;
use Illuminate\Support\Str;
use App\Http\Models\ItemGift;
use App\Http\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends BaseModel
{
    use HasFactory;

    protected $table = 'addresses';
    protected $fillable = ['user_id', 'province_id', 'city_id', 'district_id', 'postal_code', 'address'];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id', 
                    'user_id', 
                    'province_id', 
                    'city_id',
                    'district_id', 
                    'postal_code', 
                    'address'
                ]);
    }
}

