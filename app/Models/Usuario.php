<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'Id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'Nombres', 'Apellido_Paterno', 'Apellido_Materno',
        'usuario', 'Correo', 'Contraseña', 'Telefono'
    ];

    protected $hidden = ['Contraseña'];

    public function getAuthPassword()
    {
        return $this->Contraseña;
    }

    public function documentos()
    {
        // CORREGIDO: Ajusta 'documentos_usuario' según tu phpMyAdmin
        return $this->hasMany(Documento::class, 'documentos_usuario', 'Id_usuario');
    }
}