<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Ejidatario;

class Parcela extends Model
{
    protected $table = 'Parcela';
    protected $primaryKey = 'Id_Parcela';
    public $timestamps = false;

    protected $fillable = [
        'No_Parcela',
        'Superficie',
        'Ubicacion',
        'Id_Uso',
        'Id_Ejidatario'
    ];

    public function ejidatario()
    {
        return $this->belongsTo(Ejidatario::class, 'Id_Ejidatario', 'Id_Ejidatario');
    }

    public function colindancia()
    {
        return $this->hasOne(Colindancia::class, 'Id_Parcela', 'Id_Parcela');
    }

    public function coordenadas()
    {
        return $this->hasMany(Coordenada::class, 'Id_Parcela', 'Id_Parcela');
    }

    public function infAdmin()
    {
        return $this->hasOne(InfAdmin::class, 'Id_Parcela', 'Id_Parcela');
    }
    public function usoSuelo()
    {
        return $this->belongsTo(TipoUsoSuelo::class, 'Id_Uso', 'Id_Uso');
    }
}
