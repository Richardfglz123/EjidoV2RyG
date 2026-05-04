<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $table = 'Evento';
    protected $primaryKey = 'Id_Evento';
    public $timestamps = false;

    protected $fillable = [
        'Nombre_Evento',
        'Id_Categoria_Evento',
        'Observaciones',
        'Id_Creo',
        'Fecha_Creo',
        'Id_Modificado',
        'Fecha_Modificado',
        'Id_Elimino',
        'Fecha_Eliminado'
    ];

    // Relación inversa con Categoría
    public function categoria()
    {
        return $this->belongsTo(Categoria_Evento::class, 'Id_Categoria_Evento', 'Id_Categoria_Evento');
    }
}