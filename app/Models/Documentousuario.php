<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoUsuario extends Model
{
    protected $table = 'Documentos_Usuario';

    protected $primaryKey = 'Id_Documento';

    public $timestamps = true;

    protected $fillable = [
        'Id_Usuario',
        'nombre_documento',
        'ruta_archivo',
        'Id_Elimino',
        'Id_Modificado',
        'Id_Creo',
        'Fecha_Eliminado',
        'Fecha_Modificado',
        'Fecha_Creo'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'Id_Usuario', 'Id_usuario');
    }
}