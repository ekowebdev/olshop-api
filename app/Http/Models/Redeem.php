<?php

namespace App\Http\Models;

use App\Http\Models\User;
use App\Http\Models\Address;
use App\Http\Models\Shipping;
use App\Http\Models\BaseModel;
use App\Http\Models\PaymentLog;
use App\Http\Models\RedeemItemGift;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Redeem extends BaseModel
{
    use HasFactory;

    protected $table = 'redeems';
    protected $fillable = ['user_id', 'address_id', 'redeem_code', 'total_point', 'shipping_fee', 'total_amount', 'note', 'redeem_date', 'snap_token', 'snap_url', 'metadata', 'redeem_status', 'deleted_at'];
    protected $dates = ['deleted_at'];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function redeem_item_gifts()
    {
        return $this->hasMany(RedeemItemGift::class);
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
                    'redeem_code', 
                    'total_point', 
                    'shipping_fee', 
                    'total_amount',
                    'redeem_date',
                    'note',
		    'snap_token',
                    'snap_url', 
                    'metadata', 
                    'redeem_status',
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
                    'redeem_code', 
                    'total_point', 
                    'shipping_fee', 
                    'total_amount',
                    'redeem_date',
                    'note',
		    'snap_token',
                    'snap_url', 
                    'metadata', 
                    'redeem_status',
                    'created_at',
                ]);
    }
}
