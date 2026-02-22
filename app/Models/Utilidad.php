<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Utilidad extends Model
{
    use HasFactory;

    protected $table = 'Utilidad';
    protected $primaryKey = 'Id_Utilidad';
    public $timestamps = false;

    protected $fillable = [
        'monto',
        'anio',
        'tipo_reparto',
        'fecha_limite',
        'fecha_registro',
        'id_user'
    ];


    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'id_utilidad', 'id_utilidad');
    }
}