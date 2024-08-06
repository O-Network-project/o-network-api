<?php

namespace App\Mail;

use App\Classes\Invitation\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Invitation that will be send.
     */
    public Invitation $invitation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->to($this->invitation->getEmail())
            ->subject("{$this->invitation->getOrganization()->name} vous invite")
            ->markdown('emails.invitation');
    }
}
