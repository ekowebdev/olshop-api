<?php

namespace App\Http\Models;

use Illuminate\Support\Str;
use App\Http\Models\ItemGift;
use App\Http\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends BaseModel
{
    use HasFactory;

    protected $table = 'categories';
    protected $fillable = ['category_code', 'category_name', 'category_slug', 'category_sort', 'category_status'];
    
    public function item_gifts()
    {
        return $this->hasOne(ItemGift::class);
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id', 
                    'category_code', 
                    'category_name', 
                    'category_slug', 
                    'category_sort',
                    'category_status',
                ]);
    }
}

