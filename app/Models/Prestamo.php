<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $table = 'prestamo';
    protected $primaryKey = 'Id_Prestamo'; // OK, coincide con la DB
    public $timestamps = false;

    protected $fillable = [
        'Fecha',
        'Cantidad',
        'Motivo',
        'Id_Ejidatario',
        'Id_Utilidad',
        'Saldo_Continuo',   // coincide con la DB
        'total_abonado',     // si la vas a usar
        'estado_prestamo',   // si la vas a usar
        'monto_original',    // si la vas a usar
    ];

    public function ejidatario()
    {
        return $this->belongsTo(Ejidatario::class, 'Id_Ejidatario');
    }

    public function utilidad()
    {
        return $this->belongsTo(Utilidad::class, 'Id_Utilidad');
    }
}