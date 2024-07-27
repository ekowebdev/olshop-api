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

    protected $email, $headerData, $detailData, $locale;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $headerData, $detailData, $locale)
    {
        $this->email = $email;
        $this->detailData = $detailData;
        $this->headerData = $headerData;
        $this->locale = $locale;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)->send(new OrderConfirmation($this->headerData, $this->detailData, $this->locale));
    }
}
