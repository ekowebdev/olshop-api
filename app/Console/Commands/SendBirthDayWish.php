<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Http\Models\User;
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
        $users = User::whereRaw('MONTH(birthdate) = ? AND DAY(birthdate) = ?', [now()->month, now()->day])->get();

        foreach ($users as $user) {
            SendEmailBirtDayWishJob::dispatch($user);
        }

        $this->info('Birthday notifications sent successfully.');
    }
}
