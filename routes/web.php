<?php

use App\Http\Models\User;
use App\Http\Models\Notification;
use Illuminate\Support\Facades\Route;
use App\Events\PublicNotificationEvent;
use App\Events\RealTimeNotificationEvent;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TestNotificationController;

Route::get('/', function(){
    return 'Baktiweb Olshop API version 1';
    // return redirect('https://baktiweb.my.id');
});

Route::get('/event', [TestNotificationController::class, 'index']);
Route::get('/send-event', [TestNotificationController::class, 'form']);
Route::post('/send-event', [TestNotificationController::class, 'send'])->name('send-notification');
Route::get('/send-event-private', function () {
    $user = User::first();
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
