<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendEmailOrderConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email, $header_data, $detail_data, $locale;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $header_data, $detail_data, $locale)
    {
        $this->email = $email;
        $this->detail_data = $detail_data;
        $this->header_data = $header_data;
        $this->locale = $locale;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)->send(new OrderConfirmation($this->header_data, $this->detail_data, $this->locale));
    }
}
