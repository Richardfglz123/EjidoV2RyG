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
    /**
     * Relación con los documentos del expediente del usuario.
     */
    public function documentos()
    {
        // Si tu modelo de Documentos se llama 'Documento'
        // Laravel asume que la llave foránea en esa tabla es 'usuario_id' o 'Id_Usuario'
        return $this->hasMany(\App\Models\Documento::class, 'Id_Usuario');
    }
}