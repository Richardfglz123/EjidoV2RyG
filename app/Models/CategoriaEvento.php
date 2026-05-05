<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaEvento extends Model
{
    protected $table = 'Categoria_Evento';
    protected $primaryKey = 'Id_Categoria_Evento';
    public $timestamps = false;
    protected $fillable = ['Nombre_Categoria'];
}