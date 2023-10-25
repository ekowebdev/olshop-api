<?php

use App\Events\NotificationEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return 'Baktiweb Olshop API versi 1';
    return view('welcome');
    // $key = 'hello';
    // dump('hello is', Cache::get($key));
    // Cache::put($key, 'cached world', now()->addMinutes(10));
    // dump('hello is', Cache::get($key));
});

Route::get('/send-event/{user}', function ($user) {
    $user = ucwords($user);
    broadcast(new NotificationEvent($user));
    return "Event berhasil dikirim";
});
