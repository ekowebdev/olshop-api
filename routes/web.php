<?php

use App\Http\Models\User;
use App\Events\NotificationEvent;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return 'Baktiweb Olshop API versi 1';
    return view('welcome');
});

Route::get('/send-event/{user}', function ($user) {
    broadcast(new NotificationEvent($user));
});
