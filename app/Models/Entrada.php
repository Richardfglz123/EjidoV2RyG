<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entrada extends Model
{
    use HasFactory;

    protected $table = 'Entrada'; // Match SQL: CREATE TABLE Entrada
    protected $primaryKey = 'Id_Entrada';
    public $timestamps = false;

    protected $fillable = [
        'Id_Articulo',   // Antes id_equipo
        'Cantidad',      // Antes cantidad
        'Observaciones', // Antes observaciones
        'Fecha'          // Antes fecha_entrada
    ];

    public function articulo()
    {
        // Especificamos: (Modelo destino, llave foránea en Entrada, llave local en Articulo)
        return $this->belongsTo(Articulo::class, 'Id_Articulo', 'Id_Articulo');
    }
}
