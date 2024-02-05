<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\App;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class BirthDayWish extends Mailable
{
    use Queueable, SerializesModels;

    public $user, $locale;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $locale = null)
    {
        $this->user = $user;
        $this->locale = $locale ?? App::getLocale();
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: trans('all.birthday_wish_title', [], $this->locale),
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.birthday_wish',
            with : [
                'user' => $this->user,
                'profile' => $this->user->profile,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
