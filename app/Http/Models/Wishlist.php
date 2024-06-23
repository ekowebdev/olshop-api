<?php

namespace App\Http\Models;

use App\Http\Models\User;
use App\Http\Models\Product;
use Jenssegers\Mongodb\Eloquent\Model;

class Wishlist extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'wishlists';
    protected $primaryKey = 'id';
    protected $fillable = ['user_id', 'product_id'];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function products()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
