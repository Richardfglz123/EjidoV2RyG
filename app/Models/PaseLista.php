<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaseLista extends Model
{
    protected $table = 'PaseLista';
    protected $primaryKey = 'Id_PaseL';
    public $timestamps = false;

    protected $fillable = [
        'Id_Sesion',
        'Id_Ejidatario',
        'Asistencia',
        'Fecha',
        'Id_Actividad'
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