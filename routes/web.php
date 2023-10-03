<?php

use App\Http\Models\User;
use App\Mail\BirthDayWish;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Baktiweb Olshop API versi 1';
});
