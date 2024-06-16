<?php

use App\Http\Models\User;
use App\Http\Models\Notification;
use Illuminate\Support\Facades\Route;
use App\Events\RealTimeNotificationEvent;

Route::get('/', function () {
    return 'Baktiweb Olshop API version 1';
});

Route::get('/event', function () {
    return view('welcome');
});

Route::get('/send-event', function () {
    $user = User::find(45);
    $data_notification = [
        'data' => [
            'user_id' => $user->id,
            'title' => 'Transaksi Berhasil',
            'text' => 'Anda telah berhasil melakukan transaksi!',
            'type' => 0,
            'status_read' => 0,
        ],
        'total_unread' => Notification::query()->orderBy('created_at', 'desc')->where('user_id', $user->id)->where('status_read', 0)->count()
    ];
    store_notification($data_notification['data']);
    broadcast(new RealTimeNotificationEvent($data_notification, $user->id));
    return "Event berhasil dikirim";
});
