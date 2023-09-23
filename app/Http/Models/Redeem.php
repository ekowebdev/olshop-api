<?php

namespace App\Http\Models;

use App\Http\Models\User;
use App\Http\Models\Shipping;
use App\Http\Models\BaseModel;
use App\Http\Models\PaymentLog;
use App\Http\Models\RedeemItemGift;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Redeem extends BaseModel
{
    use HasFactory;

    protected $table = 'redeems';
    protected $fillable = ['user_id', 'redeem_code', 'total_point', 'redeem_date', 'snap_url', 'metadata', 'redeem_status'];

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
        return $this->hasMany(PaymentLog::class);
    }

    public function shippings()
    {
        return $this->hasOne(Shipping::class);
    }

    public function scopeGetAll($query)
    {      
        return $query->select([
                    'id', 
                    'user_id', 
                    'redeem_code', 
                    'total_point', 
                    'redeem_date',
                    'snap_url', 
                    'metadata', 
                    'redeem_status',
                ]);
    }
}
