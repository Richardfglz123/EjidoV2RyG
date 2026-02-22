<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogoMulta extends Model
{
    use HasFactory;

    protected $table = 'catalogo_multa';
    protected $primaryKey = 'id_multa_c';
    public $timestamps = false;


    protected $fillable = [
        'monto',
        'anio',
        'tipo',
        'fecha_registro',
        'id_user'
    ];
}