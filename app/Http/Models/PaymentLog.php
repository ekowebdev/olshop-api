<?php

namespace App\Http\Models;

use App\Http\Models\Order;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentLog extends BaseModel
{
    use HasFactory;

    protected $table = 'payment_logs';
    protected $fillable = ['type', 'order_id', 'status', 'raw_response'];

    public function orders()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function scopeGetAll($query)
    {      
        return $query->select([
                    'id',
                    'type', 
                    'order_id', 
                    'status', 
                    'raw_response'
                ]);
    }
}
