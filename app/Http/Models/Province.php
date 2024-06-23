<?php

namespace App\Http\Models;

use App\Http\Models\City;
use App\Http\Models\Address;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Province extends BaseModel
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'provinces';
    protected $fillable = ['name'];

    public function address()
    {
        return $this->hasMany(Address::class);
    }

    public function city()
    {
        return $this->hasMany(City::class);
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id',
                    'name',
                ]);
    }
}

