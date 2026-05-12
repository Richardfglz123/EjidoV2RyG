<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaseLista extends Model
{
    protected $table = 'PaseLista';
    protected $primaryKey = 'Id_Asistencia';
    public $timestamps = false;

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

    public function ejidatario()
    {
        return $this->belongsTo(Ejidatario::class, 'Id_Ejidatario', 'Id_Ejidatario');
    }
}