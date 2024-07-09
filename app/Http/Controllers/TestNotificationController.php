<?php

namespace App\Http\Controllers;

use App\Http\Models\User;
use Illuminate\Http\Request;
use App\Http\Models\Notification;
use App\Events\PublicNotificationEvent;
use App\Events\RealTimeNotificationEvent;
use App\Http\Resources\NotificationResource;
use Illuminate\Pagination\LengthAwarePaginator;

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
        $locale = 'id';

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

        $allNotifications = store_notification($input);

        $results['data'] = $allNotifications;
        $results['total_unread'] = Notification::Unread()->where('user_id', $user->id)->count();

        broadcast(new RealTimeNotificationEvent($results->toArray(), $user->id));

        return response()->json(['status' => 'Notification sent successfully!'], 200);
    }
}
