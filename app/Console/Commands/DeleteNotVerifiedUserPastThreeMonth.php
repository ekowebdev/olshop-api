<?php

namespace App\Console\Commands;

use App\Http\Models\User;
use Illuminate\Console\Command;

class DeleteNotVerifiedUserPastThreeMonth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for delete not verified users in a past 3 month';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $i = 0;
        $expired = now()->subMonth(3)->format('Y-m-d H:i:s');
        $users = User::where('email_verified_at', '=', null)->where('created_at', '<', $expired)->get();

        foreach($users as $user) {
            $user->delete();
            $i++;
        }

        $this->info($i . ' Deleted users successfully.');
    }
}
