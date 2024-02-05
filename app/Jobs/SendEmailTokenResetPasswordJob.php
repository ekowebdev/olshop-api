<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Mail\TokenResetPassword;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendEmailTokenResetPasswordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email, $data, $locale;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $data, $locale)
    {
        $this->email = $email;
        $this->data = $data;
        $this->locale = $locale;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)->send(new TokenResetPassword($this->data, $this->locale));
    }
}
