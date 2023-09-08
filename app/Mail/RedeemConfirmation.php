<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RedeemConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $transaction_details;
    protected $item_details;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($transaction_details, $item_details)
    {
        $this->transaction_details = $transaction_details;
        $this->item_details = $item_details;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Order Confirmation',
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
                'transaction_details' => $this->transaction_details,
                'item_details' => $this->item_details
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
