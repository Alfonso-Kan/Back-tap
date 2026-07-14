<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CredencialesAcceso extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $usuario,
        public string $password,
        public bool $esNuevoUsuario = false,
    ) {}

    public function build(): self
    {
        $asunto = $this->esNuevoUsuario
            ? 'Bienvenido a Tap Demo - Tus credenciales de acceso'
            : 'Recuperación de contraseña - Tap Demo';

        return $this->subject($asunto)->view('emails.credenciales-acceso');
    }
}
