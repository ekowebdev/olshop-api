<?php

namespace App\Http\Models;

use App\Http\Models\Review;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReviewFile extends BaseModel
{
    use HasFactory;

    protected $table = 'review_files';
    protected $fillable = ['review_id', 'review_file'];
    protected $appends = ['review_file_url'];

    public function getReviewFileUrlAttribute()
    {
        if ($this->review_file != null) {
            $url = 'https://'. env('AWS_BUCKET') .'.s3-'. env('AWS_DEFAULT_REGION') .'.amazonaws.com/files/reviews/' . $this->review_file;
        }
        return $url ?? null;
    }

    public function reviews()
    {
        return $this->belongsTo(Review::class, 'review_id');
    }

    public function scopeGetAll($query)
    {      
        return $query->select([
                    'id',
                    'review_id',
                    'review_file',
                ]);
    }
}
