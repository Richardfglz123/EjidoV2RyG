<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InicioController extends Controller
{
    public function index()
    {
        $anioActual = Carbon::now()->year;

        $totalEjidatarios = DB::table('Ejidatario')->count();

        $totalParcelas = DB::table('Parcela')->count();

        $faenasDelMes = DB::table('Evento as e')
            ->join('Categoria_Evento as c', 'e.Id_Categoria_Evento', '=', 'c.Id_Categoria_Evento')
            ->where('c.Nombre_Categoria', 'LIKE', '%Faena%')
            ->whereMonth('e.Fecha_Creo', Carbon::now()->month)
            ->whereYear('e.Fecha_Creo', $anioActual)
            ->count();

        $actividad = DB::table('Actividad as a')
            ->leftJoin('usuario as u', 'a.Id_Creo', '=', 'u.Usuario')
            ->select('a.*', 'u.Usuario as NombreUsuario')
            ->orderByDesc('a.Fecha_Creo')
            ->limit(5)
            ->get();

        $proximosEventos = DB::table('Evento as e')
            ->join('Categoria_Evento as c', 'e.Id_Categoria_Evento', '=', 'c.Id_Categoria_Evento')
            ->select('e.*', 'c.Nombre_Categoria')
            ->orderByDesc('e.Fecha_Creo')
            ->limit(5)
            ->get();

        return view('cpanel.inicio', [
            'totalEjidatarios'  => $totalEjidatarios,
            'totalParcelas'     => $totalParcelas,
            'faenasDelMes'      => $faenasDelMes,
            'actividadReciente' => $actividad,
            'proximosEventos'   => $proximosEventos,
            'anioActual'        => $anioActual,
            'user_name'         => session('nombre_completo'),
            'user_email'        => session('user_email'),
            'rol'               => session('rol'),
        ]);
    }
}