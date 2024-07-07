<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Models\Notification;
use App\Http\Models\User;
use App\Events\PublicNotificationEvent;
use App\Events\RealTimeNotificationEvent;

class TestNotificationController extends Controller
{
    public function index()
    {
        return view('notification');
    }

    public function form()
    {
        return view('send-notification');
    }

    public function send(Request $request)
    {
        $message = $request->input('message');
        broadcast(new PublicNotificationEvent($message))->toOthers();
        return response()->json(['status' => 'Message sent successfully!'], 200);
    }

    public function formPrivate()
    {
        $users = User::all();
        return view('send-notification-private', ['users' => $users]);
    }

    public function sendPrivate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::find($request->user_id);

        $input = [
            'user_id' => $user->id,
            'title' => 'Transaksi Berhasil',
            'text' => 'Anda telah berhasil melakukan transaksi!',
            'type' => 0,
            'status_read' => 0,
        ];

        $notification = store_notification($input);

        $data = [];
        $data['data'] = $notification;
        $data['total_unread'] = Notification::where('user_id', $user->id)->where('status_read', 0)->count();

        broadcast(new RealTimeNotificationEvent($data, $user->id));

        return response()->json(['status' => 'Notification sent successfully!'], 200);
    }
}
