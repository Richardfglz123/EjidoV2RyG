<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $table = 'Prestamo';
    protected $primaryKey = 'Id_Prestamo';

    // Desactivamos timestamps porque tu tabla no tiene created_at/updated_at
    public $timestamps = false;

    protected $fillable = [
        'Fecha',
        'Cantidad',
        'Motivo', // Agregado correctamente
        'Id_Ejidatario',
        'Id_Utilidad',
        // 'Saldo_Continuo', // Asegúrate de que esta columna exista en la BD
    ];

    // Ayuda a Laravel a manejar los tipos de datos correctamente
    protected $casts = [
        'Fecha' => 'date',
        'Cantidad' => 'decimal:2',
    ];

    public function ejidatario()
    {
        return $this->belongsTo(Ejidatario::class, 'Id_Ejidatario');
    }

    public function utilidad()
    {
        // Asegúrate de que el modelo Utilidad exista y use 'Id_Utilidad' como PK
        return $this->belongsTo(Utilidad::class, 'Id_Utilidad');
    }
}