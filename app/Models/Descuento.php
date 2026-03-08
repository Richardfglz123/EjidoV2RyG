<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Descuento extends Model
{
    use HasFactory;

    protected $table = 'Descuentos';
    protected $primaryKey = 'Id_Descuento';
    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'Descuento',
        'Id_Ejidatario',
        'Id_MultaC',
        'Id_Creo',
        'Fecha_Creo'
    ];

    public function setTipoAttribute($value)
    {
        $this->attributes['tipo'] = trim($value);
    }

    public function ejidatario()
    {
        return $this->belongsTo(Ejidatario::class, 'Id_Ejidatario', 'Id_Ejidatario');
    }
}