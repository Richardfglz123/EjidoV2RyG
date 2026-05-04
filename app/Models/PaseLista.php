<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaseLista extends Model
{
    protected $table = 'PaseLista';
    // Si tu llave primaria no se llama 'id', especifícala (ejemplo: 'Id_Pase')
    // protected $primaryKey = 'Id_Pase';

    public $timestamps = false;

    protected $fillable = [
        'Id_Sesion',
        'Id_Ejidatario',
        'Estatus', // Por ejemplo: 'Asistencia', 'Falta', 'Retraso'
        'Fecha_Creo',
        'Id_Creo'
    ];

    /**
     * Relación: Un registro de pase de lista pertenece a una sesión.
     */
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