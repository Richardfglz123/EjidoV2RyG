<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoMulta extends Model
{
    protected $table = 'Catalogo_Multa';
    protected $primaryKey = 'Id_MultaC';

    public $timestamps = false;

    protected $fillable = [
        'Año',
        'Tipo',
        'Costo'
    ];
}