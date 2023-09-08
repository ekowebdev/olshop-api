<?php

namespace App\Http\Models;

use App\Http\Models\Redeem;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentLog extends BaseModel
{
    use HasFactory;

    protected $table = 'payment_logs';
    protected $fillable = ['payment_type', 'redeem_id', 'payment_status', 'raw_response'];

    public function redeems()
    {
        return $this->belongsTo(Redeem::class, 'redeem_id');
    }

    public function scopeGetAll($query)
    {      
        return $query->select([
                    'id',
                    'payment_type', 
                    'redeem_id', 
                    'payment_status', 
                    'raw_response'
                ]);
    }
}
