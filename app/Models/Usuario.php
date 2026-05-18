<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // 1. Indica el nombre real de tu tabla
    protected $table = 'usuario';

    // 2. INDICA TU LLAVE PRIMARIA (Esto arregla el error del tokenable_id)
    protected $primaryKey = 'Id_Usuario';

    // 3. Si tu Id_Usuario es un INT autoincrementable, asegúrate de esto:
    public $incrementing = true;

    // 4. Desactiva timestamps si no tienes las columnas 'created_at' y 'updated_at'
    // o mapealas si tienen nombres distintos (como Fecha_Creo)
    const CREATED_AT = 'Fecha_Creo';
    const UPDATED_AT = 'Fecha_Modificado';

    protected $fillable = [
        'Nombres', 'Apellido_Paterno', 'Apellido_Materno',
        'Usuario', 'Correo', 'Contraseña', 'Telefono'
    ];

    protected $hidden = [
        'Contraseña',
    ];
}