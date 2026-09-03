<?php

namespace App\Mail;

use App\Models\Negocio;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NegocioInvitacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Negocio $negocio,
        public string $invitationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Invitación a '.$this->negocio->nombre.' en ACSoft');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.business-invitation');
    }
}
