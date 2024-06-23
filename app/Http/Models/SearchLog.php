<?php

namespace App\Http\Models;

use App\Http\Models\User;
use Jenssegers\Mongodb\Eloquent\Model;

class SearchLog extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'search_logs';
    protected $primaryKey = 'id';
    protected $fillable = ['user_id', 'search_text'];

    public function scopeLastMonth($query)
    {
        $date = now()->subMonth()->format('Y-m-d H:i:s');
        return $query->where('created_at', '>=', $date);
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
