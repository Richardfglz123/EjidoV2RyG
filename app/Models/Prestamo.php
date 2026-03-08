<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $table = 'Prestamo';
    protected $primaryKey = 'Id_Prestamo';
    public $timestamps = false;

    protected $fillable = [
        'Fecha',
        'Cantidad',
        'Motivo',
        'Id_Ejidatario',
        'Id_Utilidad',
        'Saldo_Continuo',
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