<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;

class Ejidatario extends Model
{
    protected $table = 'Ejidatario';
    protected $primaryKey = 'Id_Ejidatario';
    public $timestamps = false;

    protected $fillable = [
        'Num_Ejidatario','qr_payload', 'Calle', 'Num_Exterior', 'Num_Interior', 'Colonia',
        'Municipio', 'Estado', 'Codigo_Postal', 'Fecha_Nacimiento', 'CURP',
        'RFC', 'Clave_Elector', 'Fecha_Ingreso', 'Id_Estatus', 'Id_usuario'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'Id_usuario', 'Id_usuario');
    }

    public function descuentos()
    {
        return $this->hasMany(Descuento::class, 'Id_Ejidatario', 'Id_Ejidatario');
    }

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'Id_Ejidatario', 'Id_Ejidatario');
    }

    public function pasesLista()
    {
        return $this->hasMany(PaseLista::class, 'Id_Ejidatario', 'Id_Ejidatario');
    }
}