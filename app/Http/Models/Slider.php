<?php

namespace App\Http\Models;

use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Slider extends BaseModel
{
    use HasFactory;

    protected $table = 'sliders';
    protected $fillable = ['image', 'title', 'description', 'link', 'sort', 'start_date', 'end_date', 'status'];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            $url = 'https://'. env('AWS_BUCKET') .'.s3-'. env('AWS_DEFAULT_REGION') .'.amazonaws.com/images/slider/' . $this->image;
        }
        return $url ?? null;
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id', 
                    'image', 
                    'title', 
                    'description', 
                    'link', 
                    'sort', 
                    'start_date', 
                    'end_date', 
                    'status',
                ]);
    }

    public function scopeGetAllActive($query)
    {
        return $query->select([
                    'id', 
                    'image', 
                    'title', 
                    'description', 
                    'link', 
                    'sort', 
                    'start_date', 
                    'end_date', 
                    'status',
                ])
                ->where('start_date', '<=', now()->format('Y-m-d'))
                ->where('end_date', '>=', now()->format('Y-m-d'))
                ->where('status', '=', 'A');
    }
}

