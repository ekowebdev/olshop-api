<?php

namespace App\Http\Models;

use App\Http\Models\User;
use App\Http\Models\Product;
use BaoPham\DynamoDb\DynamoDbModel;

class Wishlist extends DynamoDbModel
{
    protected $primaryKey = 'id';
    protected $fillable = ['user_id', 'product_id'];

    public function getTable()
    {
        $table = config('app.env') === 'local' ? 'local_wishlists' : 'wishlists';
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
}
