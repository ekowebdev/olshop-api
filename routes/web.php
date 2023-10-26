<?php

use App\Events\TestEvent;
use App\Events\NotificationEvent;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

Route::get('/', function () {
    // return 'Baktiweb Olshop API versi 1';
    return view('welcome');
});

Route::get('/send-event', function () {
    // $user = ucwords($user);
    // broadcast(new TestEvent($user));
    $notification = [];
    $notification['user_id'] = 14;
    $notification['title'] = 'Transaksi Berhasil';
    $notification['text'] = 'Anda telah berhasil melakukan transaksi!';
    $notification['type'] = 0;
    $notification['status_read'] = 0;
    $data_notification = store_notification($notification);
    broadcast(new NotificationEvent($data_notification));
    return "Event berhasil dikirim";
});
