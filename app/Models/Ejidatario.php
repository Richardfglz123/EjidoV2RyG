<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ejidatario extends Model
{
    protected $table = 'Ejidatario';
    protected $primaryKey = 'Id_Ejidatario';
    public $timestamps = false;

    protected $fillable = [
        'Num_Ejidatario',
        'Calle',
        'Num_Exterior',
        'Num_Interior',
        'Colonia',
        'Municipio',
        'Estado',
        'Codigo_Postal',
        'Fecha_Nacimiento',
        'CURP',
        'RFC',
        'Clave_Elector',
        'Fecha_Ingreso',
        'Id_Estatus',
        'Id_Usuario'
    ];
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'Id_Usuario', 'Id_Usuario');
    }

}
