<?php

namespace App\Http\Controllers;

use App\Models\Sesion;
use App\Models\PaseLista; // Asegúrate de tener este modelo creado
use Illuminate\Http\Request;

class PaseListaController extends Controller
{
    public function index()
    {
        // Aquí cargarías la vista para tomar lista
        return view('cpanel.PaseLista.paselista');
    }

    public function registrarAsistencia(Request $request)
    {
        // 1. Crear o buscar la sesión única
        $sesion = Sesion::firstOrCreate(
            [
                'Tipo' => $request->tipo, // 'Evento' o 'Actividad'
                'Id_Referencia' => $request->id_referencia,
                'Fecha' => $request->fecha
            ]
        );

        // 2. Guardar el registro de asistencia
        // Nota: Asegúrate de que el modelo PaseLista exista
        /*
        PaseLista::create([
            'Id_Sesion' => $sesion->Id_Sesion,
            'Id_Ejidatario' => $request->id_ejidatario,
            'Estatus' => $request->estatus,
            'Fecha_Creo' => now(),
        ]);
        */

        return back()->with('success', 'Asistencia registrada en la sesión #' . $sesion->Id_Sesion);
    }
}