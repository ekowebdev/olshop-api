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

    public $notification, $userId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($notification, $userId)
    {
        $this->notification = $notification;
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('Order.User.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'order-user';
    }

    public function broadcastWith()
    {
        return [
            'data' => $this->notification
        ];
    }
}
