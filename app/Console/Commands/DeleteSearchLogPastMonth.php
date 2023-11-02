<?php

namespace App\Console\Commands;

use App\Http\Models\SearchLog;
use Illuminate\Console\Command;

class DeleteSearchLogPastMonth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:searchlogs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for delete search logs in a past month';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $i = 0;
        $startDate = now()->subMonth()->format('Y-m-d H:i:s');
        $search_logs = SearchLog::query()
                        ->where('created_at', '<', $startDate)
                        ->get();

        foreach ($search_logs as $search_log) {
            $search_log->delete();
            $i++;
        }

        $this->info($i . ' Deleted search logs successfully.');
    }
}
