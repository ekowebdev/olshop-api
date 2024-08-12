<?php

namespace App\Http\Models;

use App\Http\Models\Order;
use App\Http\Models\Variant;
use App\Http\Models\Product;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderProduct extends BaseModel
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'order_products';
    protected $fillable = ['order_id', 'product_id', 'variant_id', 'quantity', 'point'];

    public function orders()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function products()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variants()
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'order_id',
                    'product_id',
                    'variant_id',
                    'quantity',
                    'point',
                    'created_at',
                    'updated_at',
                ]);
    }
}
