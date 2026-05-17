<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Postulacion extends Model
{
    use HasFactory;

    protected $table = 'postulaciones';
    protected $primaryKey = 'id_postulacion';
    public $timestamps = false;

    protected $fillable = [
        'id_programa',
        'id_usuario',
        'ruta_ine',
        'ruta_curp',
        'ruta_comprobante',
        'fecha_postulacion',
        'estado'
    ];

    public function usuario()
    {
        return $this->belongsTo(usuario::class, 'id_usuario');
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class, 'id_programa');
    }

    public function evidencias()
    {
        return $this->hasMany(PostulacionEvidencia::class, 'id_postulacion', 'id_postulacion');
    }
}