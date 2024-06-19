<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\PublicNotificationEvent;

class TestNotificationController extends Controller
{
    public function index(){
        return view('notification');
    }

    public function form(){
        return view('send-notification');
    }

    public function send(Request $request){
        $message = $request->input('message');
        broadcast(new PublicNotificationEvent($message))->toOthers();
        return redirect()->back()->with('status', 'Message sent successfully!');
    }
}
