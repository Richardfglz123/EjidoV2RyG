<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documentousuario extends Model
{
    protected $table = 'documentos_usuario';

    protected $primaryKey = 'Id_documento';

    public $timestamps = false;

    protected $fillable = ['Id_usuario', 'nombre_documento', 'ruta_archivo', 'validado'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'Id_usuario', 'Id_usuario');
    }
}