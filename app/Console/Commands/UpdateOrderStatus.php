<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Http\Models\Order;
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
        $threshold = Carbon::now()->subHours(24);
        $orders = Order::where('status', 'pending')->where('created_at', '<', $threshold)->get();

        foreach($orders as $order) {
            $order->status = 'failure';
            $order->save();
            $i++;
        }

        $this->info($i . ' Updated order status successfully.');
    }
}
