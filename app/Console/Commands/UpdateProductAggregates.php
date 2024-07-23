<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateProductAggregatesJob;

class UpdateProductAggregates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:product-aggregates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update product aggregates for total order, total rating, and total review';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        UpdateProductAggregatesJob::dispatch();
        $this->info('Updated product aggregates successfully.');
    }
}
