<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestNotificationController;

Route::get('/', function() {
    return 'Online Shop API version 1';
    // return redirect('https://baktiweb.my.id');
});

// Route::get('/event', [TestNotificationController::class, 'index']);
// Route::get('/send-event', [TestNotificationController::class, 'form']);
// Route::post('/send-event', [TestNotificationController::class, 'send'])->name('send-notification');
// Route::get('/send-event-private', [TestNotificationController::class, 'formPrivate']);
// Route::post('/send-event-private', [TestNotificationController::class, 'sendPrivate'])->name('send-notification-private')->middleware('throttle:20,1');
