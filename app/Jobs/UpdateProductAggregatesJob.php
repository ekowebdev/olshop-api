<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateProductAggregatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \DB::statement('
            UPDATE products
            SET
                total_order = COALESCE((SELECT SUM(order_products.quantity) FROM order_products WHERE order_products.product_id = products.id), 0),
                total_review = COALESCE((SELECT COUNT(*) FROM reviews WHERE reviews.product_id = products.id), 0),
                total_rating = COALESCE((SELECT ROUND(AVG(rating), 1) FROM reviews WHERE reviews.product_id = products.id), 0.0)
        ');
    }
}
