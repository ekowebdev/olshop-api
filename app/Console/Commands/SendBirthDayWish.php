<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Mail\BirthDayWish;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendEmailBirtDayWishJob;

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
        $users = User::where('birthdate', '!=', null)->get();

        foreach ($users as $user) {
            if (Carbon::parse($user['birthdate'])->isBirthday()) {
                SendEmailBirtDayWishJob::dispatch($user);
            }
        }

        return 0;
    }
}
