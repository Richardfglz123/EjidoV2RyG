<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coordenada extends Model
{
    protected $table = 'Coordenada';
    public $timestamps = false;

    protected $fillable = [
        'Punto','CoordenadaX','CoordenadaY','Id_Parcela'
    ];

    public function parcela()
    {
        return $this->belongsTo(Parcela::class, 'Id_Parcela', 'Id_Parcela');
    }
}
