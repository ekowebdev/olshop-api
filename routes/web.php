<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Events\RealTimeNotificationEvent;
use Illuminate\Support\Facades\Broadcast;

Route::get('/', function () {
    // return 'Baktiweb Olshop API versi 1';
    return view('welcome');
});

Route::get('/send-event', function () {
    $data_notification = [
        'user_id' => 14,
        'title' => 'Transaksi Berhasil',
        'text' => 'Anda telah berhasil melakukan transaksi!',
        'type' => 0,
        'status_read' => 0,
    ];
    $notification = store_notification($data_notification);
    broadcast(new RealTimeNotificationEvent($notification, 14));
    return "Event berhasil dikirim";
});
