<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaseLista extends Model
{
    protected $table = 'PaseLista';

    // Si tu llave primaria no es 'id', DEBES declararla aquí:
    protected $primaryKey = 'Id_Asistencia';

    public $timestamps = false; // Si no usas created_at y updated_at


    protected $fillable = [
        'Id_Sesion',
        'Id_Ejidatario',
        'Estatus',
        'Fecha_Creo',
        'Id_Creo'
    ];
    public function sesion()
    {
        return $this->belongsTo(Sesion::class, 'Id_Sesion', 'Id_Sesion');
    }

    /**
     * Relación: Un registro de pase de lista pertenece a un ejidatario.
     */
    public function ejidatario()
    {
        // Asegúrate de que el modelo Ejidatario exista y coincida el nombre
        return $this->belongsTo(Ejidatario::class, 'Id_Ejidatario', 'Id_Ejidatario');
    }
}