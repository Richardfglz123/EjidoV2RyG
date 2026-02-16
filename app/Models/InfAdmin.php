<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfAdmin extends Model
{
    protected $table = 'infadmin';
    protected $primaryKey = 'Id_InfAdmin';
    public $timestamps = false;

    protected $fillable = [
        'Id_Parcela',
        'Num_InscripcionRAN',
        'ClaveNucleoAgrario',
        'Comunidad',
        'FechaExpedicion'
    ];

    public function parcela()
    {
        return $this->belongsTo(Parcela::class, 'Id_Parcela', 'Id_Parcela');
    }
}
