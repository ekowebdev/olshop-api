<?php

namespace App\Http\Models;

use BaoPham\DynamoDb\DynamoDbModel;

class SearchLog extends DynamoDbModel
{
    protected $table = 'search_logs';
    protected $primaryKey = 'id';
    protected $fillable = ['user_id', 'search_text'];
}

