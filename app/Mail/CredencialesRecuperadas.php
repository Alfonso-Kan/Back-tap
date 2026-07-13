<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CredencialesRecuperadas extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $usuario,
        public string $password,
    ) {}

    public function build(): self
    {
        return $this->subject('Recuperación de contraseña - Tap Demo')
            ->view('emails.credenciales-recuperadas');
    }
}
