<?php

use Carbon\Carbon;
use App\Http\Models\User;
use App\Mail\BirthDayWish;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendEmailBirtDayWishJob;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Baktiweb Olshop API versi 1';
});
