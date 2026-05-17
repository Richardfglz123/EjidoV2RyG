<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documentousuario extends Model
{
    protected $table = 'Documentos_usuario';
    protected $fillable = ['Id_usuario', 'nombre_documento', 'ruta_archivo', 'validado'];

    public function usuario()
    {
        return $this->belongsTo(usuario::class, 'Id_usuario', 'Id_usuario');
    }
}