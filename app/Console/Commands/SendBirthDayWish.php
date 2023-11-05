<?php

namespace App\Console\Commands;

use App\Http\Models\User;
use App\Mail\BirthDayWish;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Events\RealTimeNotificationEvent;

class SendBirthDayWish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:birthdaywish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for sending birthday wish to user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $i = 0;

        $users = User::whereHas('profile', function ($query) {
            $query->whereMonth('birthdate', '=', date('m'))
                ->whereDay('birthdate', '=', date('d'));
        })->get();
        
        foreach($users as $user) {
            Mail::to($user->email)->send(new BirthDayWish($user));
            $data_notification = [
                'user_id' => $user->id,
                'title' => trans('all.notification_birthday_title', ['name' => $user->name]),
                'text' => trans('all.notification_birthday_text'),
                'type' => 1,
                'status_read' => 0,
            ];
            $notification = store_notification($data_notification);
            broadcast(new RealTimeNotificationEvent($notification, $user->id));
            $i++;
        }

        $this->info($i.' Birthday wish messages sent successfully.');
    }
}
