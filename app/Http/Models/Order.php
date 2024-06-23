<?php

namespace App\Http\Models;

use App\Http\Models\User;
use App\Http\Models\Address;
use App\Http\Models\Shipping;
use App\Http\Models\BaseModel;
use App\Http\Models\PaymentLog;
use App\Http\Models\OrderProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends BaseModel
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'orders';
    protected $fillable = ['user_id', 'address_id', 'code', 'total_point', 'shipping_fee', 'total_amount', 'note', 'date', 'snap_token', 'snap_url', 'metadata', 'status', 'deleted_at'];
    protected $dates = ['deleted_at'];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order_products()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function payment_logs()
    {
        return $this->hasOne(PaymentLog::class);
    }

    public function shippings()
    {
        return $this->hasOne(Shipping::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id',
                    'user_id',
                    'address_id',
                    'code',
                    'total_point',
                    'shipping_fee',
                    'total_amount',
                    'date',
                    'note',
		            'snap_token',
                    'snap_url',
                    'metadata',
                    'status',
                    'created_at',
                ])
                ->whereNull('deleted_at');
    }

    public function scopeGetAllWithTrashed($query)
    {
        return $query->select([
                    'id',
                    'user_id',
                    'address_id',
                    'code',
                    'total_point',
                    'shipping_fee',
                    'total_amount',
                    'date',
                    'note',
		            'snap_token',
                    'snap_url',
                    'metadata',
                    'status',
                    'created_at',
                ]);
    }
}
