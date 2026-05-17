<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'Usuario';
    protected $primaryKey = 'Id_Usuario';
    public $timestamps = false;

    protected $fillable = [
        'Nombres', 'Apellido_Paterno', 'Apellido_Materno',
        'Usuario', 'Correo', 'Contraseña', 'Telefono'
    ];

    protected $hidden = ['Contraseña'];

    public function getAuthPassword()
    {
        return $this->Contraseña;
    }

    public function documentos()
    {
        return $this->hasMany(\App\Models\DocumentoUsuario::class, 'Id_Usuario');
    }
}