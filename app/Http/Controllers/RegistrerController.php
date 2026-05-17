<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RegistrerController extends Controller
{

    public function RegistrarEjidos(){
        return "Parcela registrada";

    }

    public function EliminarEjido(){
        return "Parcela eliminada";
    }

    public function Actualizarusuario(){
        return "Parcela actualizada";
    }

    public function Mostrarusuario(){
        return "Mostrar parcela";
    }
}
