<?php

namespace App\Http\Models;

use App\Http\Models\User;
use App\Http\Models\Product;
use App\Http\Models\Variant;
use Jenssegers\Mongodb\Eloquent\Model;

class Cart extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'carts';
    protected $primaryKey = 'id';
    protected $fillable = ['user_id', 'product_id', 'variant_id', 'quantity'];
    protected $dates = ['created_at', 'updated_at'];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function products()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variants()
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }
}
