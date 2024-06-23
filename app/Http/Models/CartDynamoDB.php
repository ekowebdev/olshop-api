<?php

namespace App\Http\Models;

use App\Http\Models\User;
use App\Http\Models\Variant;
use App\Http\Models\Product;
use BaoPham\DynamoDb\DynamoDbModel;

class CartDynamoDB extends DynamoDbModel
{
    protected $primaryKey = 'id';
    protected $fillable = ['user_id', 'product_id', 'variant_id', 'quantity'];
    protected $dates = ['created_at', 'updated_at'];

    public function getTable()
    {
        $table = config('app.env') === 'local' ? 'local_carts' : 'carts';
        return $table;
    }

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
