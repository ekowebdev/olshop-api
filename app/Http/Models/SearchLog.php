<?php

namespace App\Http\Models;

use BaoPham\DynamoDb\DynamoDbModel;

class SearchLog extends DynamoDbModel
{
    protected $table = 'search_logs';
    protected $primaryKey = 'id';
    protected $fillable = ['user_id', 'search_text'];

    public function scopeLastMonth($query)
    {
        $endDate = now()->subMonth()->format('Y-m-d H:i:s');
        return $query->where('created_at', '>=', $endDate);
    }
}

