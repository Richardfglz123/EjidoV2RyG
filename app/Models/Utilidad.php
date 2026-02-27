<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Utilidad extends Model
{
    use HasFactory;

    protected $table = 'Utilidad';
    protected $primaryKey = 'Id_Utilidad';
    public $timestamps = false;

    protected $fillable = [
        'Año',
        'UtilidadAnual',
        'Primer_Reparto',
        'SegundoReparto',
        'Reparto_Finiquito',
        'Fecha_Eliminado',
        'Fecha_Modificado',
        'Fecha_Creo',
        'Id_Elimino',
        'Id_Modificado',
        'Id_Creo',
        'Fecha_Limite'
    ];

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'Id_Utilidad', 'Id_Utilidad');
    }
}