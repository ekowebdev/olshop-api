<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class BaseController extends Controller
{
    public function __construct()
    {
        DB::enableQueryLog();
    }
}