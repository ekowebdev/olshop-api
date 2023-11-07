<?php

namespace App\Console\Commands;

use App\Http\Models\Cart;
use Illuminate\Console\Command;

class DeleteCartUserPastMonth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:carts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for delete carts each users in a past month';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $i = 0;
        $expired = now()->subMonth()->format('Y-m-d H:i:s');
        $carts = Cart::where('created_at', '<', $expired)->get();

        foreach($carts as $cart) {
            $cart->delete();
            $i++;
        }

        $this->info($i . ' Deleted carts successfully.');
    }
}
