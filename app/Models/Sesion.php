<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sesion extends Model
{
    protected $table = 'Sesion';
    protected $primaryKey = 'Id_Sesion';
    public $timestamps = false;

    protected $fillable = [
        'Tipo',
        'Id_Referencia',
        'Fecha'
    ];

    public function pasesLista()
    {
        return $this->hasMany(PaseLista::class, 'Id_Sesion', 'Id_Sesion');
    }
// En app/Models/Sesion.php

    public function asistencias()
    {
        return $this->hasMany(PaseLista::class, 'Id_Sesion', 'Id_Sesion');
    }
    public function evento()
    {
        return $this->belongsTo(Evento::class, 'Id_Referencia', 'Id_Evento');
    }

}