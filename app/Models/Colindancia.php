<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Colindancia extends Model
{
    protected $table = 'Colindancia';
    public $timestamps = false;

    protected $fillable = [
        'norte','sur','este','oeste',
        'noreste','noroeste','sureste','suroeste',
        'Id_Parcela'
    ];



    public function parcela()
    {
        return $this->belongsTo(Parcela::class, 'Id_Parcela', 'Id_Parcela');
    }
}
