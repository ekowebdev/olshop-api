<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class RealTimeNotificationEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification, $user_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($notification, $user_id)
    {
        $this->notification = $notification;
        $this->user_id = $user_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('Order.User.' . $this->user_id);
    }

    public function broadcastAs()
    {
        return 'order-user';
    }

    public function broadcastWith()
    {
        return [
            'data' => $this->notification['data'],
            'total_unread' => $this->notification['total_unread']
        ];
    }
}
