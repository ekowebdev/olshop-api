<?php

namespace App\Http\Models;

use App\Http\Models\Redeem;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipping extends BaseModel
{
    use HasFactory;

    protected $table = 'shippings';
    protected $fillable = ['redeem_id', 'origin', 'destination', 'weight', 'courier', 'service', 'description', 'cost', 'etd'];

    public function redeem()
    {
        return $this->belongsTo(Redeem::class, 'redeem_id');
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id', 
                    'redeem_id', 
                    'origin', 
                    'destination', 
                    'weight', 
                    'courier', 
                    'service', 
                    'description', 
                    'cost', 
                    'etd',
                ]);
    }
}
