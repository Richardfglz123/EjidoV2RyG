<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class InicioController extends Controller
{
    public function index()
    {
        $totalEjidatarios = DB::table('Ejidatario')->count();

        // Actividad reciente (lo último que se registró en el sistema)
        $actividad = DB::table('Actividad')
            ->orderByDesc('Fecha_Creo')
            ->limit(5)
            ->get();

        // Próximos eventos (actividades con fecha futura)
        $proximosEventos = DB::table('Actividad')
            ->whereDate('FechaInicio', '>=', now()->toDateString())
            ->orderBy('FechaInicio')
            ->limit(5)
            ->get();

        return view('cpanel.inicio', [
            'totalEjidatarios' => $totalEjidatarios,
            'actividad'        => $actividad,
            'proximosEventos'  => $proximosEventos,
            'user_name'        => session('nombre_completo'),
            'user_email'       => session('user_email'),
            'rol'              => session('rol'),
        ]);
    }

}
