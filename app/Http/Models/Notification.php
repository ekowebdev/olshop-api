<?php

namespace App\Http\Models;

use App\Http\Models\User;
use Jenssegers\Mongodb\Eloquent\Model;

class Notification extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'notifications';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'title',
        'text',
        'url',
        'user_id',
        'type',
        'icon',
        'background_color',
        'status_read',
    ];

    public function scopeRead($query)
    {
        return $query->where('status_read', '=', 1);
    }

    public function scopeUnread($query)
    {
        return $query->where('status_read', '=', 0);
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

