<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria_Evento extends Model
{
    protected $table = 'Categoria_Evento';
    protected $primaryKey = 'Id_Categoria_Evento';
    public $timestamps = false;

    protected $fillable = [
        'Clave_Categoria',
        'Nombre_Categoria'
    ];

    // Relación con Eventos
    public function eventos()
    {
        return $this::hasMany(Evento::class, 'Id_Categoria_Evento', 'Id_Categoria_Evento');
    }
}