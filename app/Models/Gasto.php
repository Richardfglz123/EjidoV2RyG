<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    protected $table = 'Gastos';
    protected $primaryKey = 'Id_Gasto';
    public $timestamps = false;

    protected $fillable = [
        'Responsable',
        'Fecha',
        'Monto',
        'Concepto',
        'Medida'
    ];
}
