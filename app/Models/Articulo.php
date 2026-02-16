<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Articulo extends Model
{
    use HasFactory;

    protected $table = 'Articulos'; // Match SQL: CREATE TABLE Articulos
    protected $primaryKey = 'Id_Articulo';
    public $timestamps = false;

    protected $fillable = [
        'Descripcion',
        'Cantidad',
        'Estado',
        'Medida',
        'Fecha_Registro'
    ];

    // Relaciones
    public function entradas() {
        return $this->hasMany(Entrada::class, 'Id_Articulo', 'Id_Articulo');
    }
    public function getDescripcionAttribute()
    {
        return $this->attributes['Descripcion'];
    }

    public function getCantidadAttribute()
    {
        return $this->attributes['Cantidad'];
    }

    public function getEstadoAttribute()
    {
        return $this->attributes['Estado'];
    }

    public function getMedidaAttribute()
    {
        return $this->attributes['Medida'];
    }

    public function getFechaRegistroAttribute()
    {
        return $this->attributes['Fecha_Registro'];
    }

}
