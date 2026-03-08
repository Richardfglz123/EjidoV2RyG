<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Utilidad extends Model
{
    protected $table = 'Utilidad';
    protected $primaryKey = 'Id_Utilidad';
    public $timestamps = false;

    protected $fillable = [
        'Monto',
        'Fecha_Limite',
        'Año',
        'Tipo_Reparto',
        'Fecha_Registro',
        'Fecha_Modificado',
        'Id_Modificado',
        'Fecha_Creo',
        'Id_Creo'
    ];
}