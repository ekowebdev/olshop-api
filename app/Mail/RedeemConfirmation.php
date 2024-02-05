<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RedeemConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $header_data, $detail_data, $locale;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($header_data, $detail_data, $locale)
    {
        $this->header_data = $header_data;
        $this->detail_data = $detail_data;
        $this->locale = $locale;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: trans('all.order_confirmation_title', $this->locale),
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
            view: 'emails.redeem_confirmation',
            with : [
                'header_data' => $this->header_data,
                'detail_data' => $this->detail_data
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
