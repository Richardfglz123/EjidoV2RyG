<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    use HasFactory;

    protected $table = 'gestion_programa';

    // ELIMINA ESTA LÍNEA:
    // protected $primaryKey = 'id_programa';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'fecha_creo',
        'fecha_eliminado',
        'id_creo'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_creo' => 'date',
        'fecha_eliminado' => 'date',
    ];
}