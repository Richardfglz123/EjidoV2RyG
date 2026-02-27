<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoUsuario extends Model
{
    protected $table = 'Documentos_Usuario';
    protected $fillable = ['Id_Usuario', 'nombre_documento', 'ruta_archivo', 'validado'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'Id_Usuario', 'Id_Usuario');
    }
}