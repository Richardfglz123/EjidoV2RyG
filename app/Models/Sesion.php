<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sesion extends Model
{
    protected $table = 'Sesion';
    protected $primaryKey = 'Id_Sesion';
    public $timestamps = false;

    protected $fillable = [
        'Tipo',         // 'Evento' o 'Actividad'
        'Id_Referencia', // El ID del Evento o de la Actividad
        'Fecha'
    ];

    // Relación con los registros de asistencia
    public function pasesLista()
    {
        return $this->hasMany(PaseLista::class, 'Id_Sesion', 'Id_Sesion');
    }
}