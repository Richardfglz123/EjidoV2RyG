<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abono extends Model
{
    protected $table = 'Abono';
    protected $primaryKey = 'Id_Abono';
    public $timestamps = false;

    protected $fillable = [
        'Id_Prestamo',
        'Monto',
        'Fecha'
    ];

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'Id_Prestamo');
    }
}