<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Descuento extends Model
{
    use HasFactory;

    // 1. Asegúrate de que sea 'descuentos' (revisa tu phpMyAdmin)
    protected $table = 'descuentos';

    protected $primaryKey = 'id_descuento';
    public $timestamps = false;

    protected $fillable = [
        'id_ejidatario',
        'tipo',
        'descuento'
    ];

    public function ejidatario()
    {
        // El tercer parámetro debe coincidir con la PK de Ejidatario: 'Id_Ejidatario'
        return $this->belongsTo(Ejidatario::class, 'id_ejidatario', 'Id_Ejidatario');
    }
}