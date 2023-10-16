<?php

namespace App\Http\Models;

use Illuminate\Support\Str;
use App\Http\Models\ItemGift;
use App\Http\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brand extends BaseModel
{
    use HasFactory;

    protected $table = 'brands';
    protected $fillable = ['brand_name', 'brand_slug', 'brand_logo', 'brand_sort'];
    protected $appends = ['brand_logo_url'];

    public function getBrandLogoUrlAttribute()
    {
        if ($this->brand_logo != null) {
            $url = 'https://'. env('AWS_BUCKET') .'.s3-'. env('AWS_DEFAULT_REGION') .'.amazonaws.com/images/brand/' . $this->brand_logo;
        }
        return $url ?? null;
    }

    public function item_gifts()
    {
        return $this->hasOne(ItemGift::class);
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id', 
                    'brand_name', 
                    'brand_slug',
                    'brand_sort',
                    'brand_logo',
                ]);
    }
}

