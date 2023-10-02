<?php

namespace App\Console\Commands;

use App\Http\Models\User;
use App\Mail\BirthDayWish;
use Illuminate\Console\Command;
use App\Jobs\SendEmailBirtDayWishJob;
use Illuminate\Support\Facades\Cache;

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
    protected $description = 'Command For Sending Birthday Wish to User';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // $users = User::whereRaw('MONTH(birthdate) = ? AND DAY(birthdate) = ?', [now()->month, now()->day])->get();

        // foreach ($users as $user) {
        //     if (!Cache::has('birthday_notification_sent_' . $user->id)) {
        //         SendEmailBirtDayWishJob::dispatch($user);
        //         Cache::put('birthday_notification_sent_' . $user->id, true, now()->addDay());
        //     }
        // }

        // $this->info('Birthday wish notifications sent successfully.');

        $i = 0;
        $users = User::whereMonth('birthdate', '=', date('m'))->whereDay('birthdate', '=', date('d'))->get();  

        foreach($users as $user) {
            SendEmailBirtDayWishJob::dispatch($user);
            $i++;
        }

        $this->info($i.' Birthday wish messages sent successfully!');
    }
}
