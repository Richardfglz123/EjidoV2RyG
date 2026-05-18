<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'usuario';

    protected $primaryKey = 'Id_Usuario';
    public $incrementing = true;

    const CREATED_AT = 'Fecha_Creo';
    const UPDATED_AT = 'Fecha_Modificado';

    protected $fillable = [
        'Nombres', 'Apellido_Paterno', 'Apellido_Materno',
        'Usuario', 'Correo', 'Contraseña', 'Telefono'
    ];

    protected $hidden = [
        'Contraseña',
    ];
    public function documentos()
    {
        return $this->hasMany(DocumentoUsuario::class, 'Id_Usuario', 'Id_Usuario');
    }
}