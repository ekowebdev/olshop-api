<?php

namespace App\Console\Commands;

use App\Http\Models\User;
use App\Mail\BirthDayWish;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

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
        $users = User::whereMonth('birthdate', '=', date('m'))->whereDay('birthdate', '=', date('d'))->get();  

        foreach($users as $user) {
            Mail::to($user->email)->send(new BirthDayWish($user));
            $i++;
        }

        $this->info($i.' Birthday wish messages sent successfully.');
    }
}
