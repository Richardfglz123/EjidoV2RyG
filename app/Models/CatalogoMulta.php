<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoMulta extends Model
{
    protected $table = 'Catalogo_Multa';
    protected $primaryKey = 'Id_MultaC';
    public $timestamps = false;

    protected $fillable = [
        'anio',        // Antes Año
        'tipo_multa',  // Antes Tipo_Multa
        'costo',       // Antes Costo
        'Id_Creo',
        'Fecha_Creo',
        'Id_Modificado',
        'Fecha_Modificado',
        'Id_Elimino',
        'Fecha_Eliminado'
    ];
}