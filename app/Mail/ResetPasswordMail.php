<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nombre;
    public $codigo;

    public function __construct($nombre, $codigo)
    {
        $this->nombre = $nombre;
        $this->codigo = $codigo;
    }

    public function build()
    {
        return $this->subject('Restablecer contraseña - Sistema Ejidal')
            ->view('cpanel.emails.reset-password');
    }
}
