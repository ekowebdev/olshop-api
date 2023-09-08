<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Mail\RedeemConfirmation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendRedeemConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $transaction_details;
    protected $item_details;
    protected $customer_details;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($customer_details, $transaction_details, $item_details)
    {
        $this->customer_details = $customer_details;
        $this->transaction_details = $transaction_details;
        $this->item_details = $item_details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->customer_details['email'])->send(new RedeemConfirmation($this->transaction_details, $this->item_details));
    }
}
