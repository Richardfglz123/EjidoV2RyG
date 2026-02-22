<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoUsuario extends Model
{
    use HasFactory;

    protected $table = 'documentos_usuario';
    protected $primaryKey = 'id_documento';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'ruta_ine',
        'ruta_curp',
        'ruta_comprobante'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}