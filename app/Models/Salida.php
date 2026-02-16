<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salida extends Model
{
    protected $table = 'Salida'; // Mayúscula según tu SQL
    protected $primaryKey = 'Id_Salida';
    public $timestamps = false;

    protected $fillable = [
        'Id_Articulo', // FK real
        'Cantidad',
        'Fecha',       // Nombre real en SQL
        'Tipo_Salida',
        'Observaciones',
        'Responsable'
    ];

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'Id_Articulo', 'Id_Articulo');
    }
}
