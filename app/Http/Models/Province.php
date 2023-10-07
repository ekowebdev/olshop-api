<?php

namespace App\Http\Models;

use App\Http\Models\Address;
use Illuminate\Support\Str;
use App\Http\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Province extends BaseModel
{
    use HasFactory;

    protected $table = 'provinces';
    protected $fillable = ['province_name'];

    public function address()
    {
        return $this->hasMany(Address::class);
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'province_id', 
                    'province_name',
                ]);
    }
}

