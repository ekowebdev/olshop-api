<?php

namespace App\Http\Models;

use App\Http\Models\User;
use BaoPham\DynamoDb\DynamoDbModel;

class SearchLog extends DynamoDbModel
{
    protected $primaryKey = 'id';
    protected $fillable = ['user_id', 'search_text'];

    public function getTable()
    {
        $table = config('app.env') === 'local' ? 'local_search_logs' : 'search_logs';
        return $table;
    }

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