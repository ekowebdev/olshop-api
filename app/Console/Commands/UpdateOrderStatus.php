<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Http\Models\Redeem;
use Illuminate\Console\Command;

class UpdateOrderStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for update order status';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $i = 0;
        $expired = Carbon::now()->subHour(24);
        $orders = Redeem::where('redeem_status', '=', 'pending')->where('created_at', '<=', $expired)->get();

        foreach($orders as $order) {
            $order->redeem_status = 'failure';
            $order->save();
            $i++;
        }

        $this->info($i . ' Update order status successfully.');
    }
}
