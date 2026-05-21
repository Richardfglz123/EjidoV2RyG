<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsambleaController extends Controller
{
    public function index(Request $request)
    {
        $anoActual = now()->year;

        $eventosAsambleas = DB::table('Evento as e')
            ->join('Categoria_Evento as c', 'e.Id_Categoria_Evento', '=', 'c.Id_Categoria_Evento')
            ->where('c.Clave_Categoria', 'LIKE', 'asamblea%')
            ->whereYear('e.Fecha_Creo', $anoActual)
            ->select('e.*')
            ->get();

        $sesionesAsambleas = DB::table('Sesion')
            ->whereIn('Id_Referencia', $eventosAsambleas->pluck('Id_Evento'))
            ->where('Tipo', 'Evento')
            ->select('Id_Sesion', 'Id_Referencia')
            ->get();

        $idsSesiones = $sesionesAsambleas->pluck('Id_Sesion')->toArray();

        $query = DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->select(
                'e.Id_Ejidatario',
                'u.Nombres',
                'u.Apellido_Paterno',
                'u.Apellido_Materno'
            );

        // Buscador
        if ($request->filled('query')) {

            $search = trim($request->get('query'));

            $query->where(function($q) use ($search) {

                $q->where('u.Nombres', 'LIKE', "%{$search}%")
                    ->orWhere('u.Apellido_Paterno', 'LIKE', "%{$search}%")
                    ->orWhere('u.Apellido_Materno', 'LIKE', "%{$search}%")
                    ->orWhere(
                        DB::raw("
                    CONCAT(
                        u.Nombres,
                        ' ',
                        u.Apellido_Paterno,
                        ' ',
                        u.Apellido_Materno
                    )
                "),
                        'LIKE',
                        "%{$search}%"
                    );
            });
        }

        $ejidatarios = $query->paginate(15);

        foreach ($ejidatarios as $ejidatario) {
            $asistenciasEnSesion = DB::table('PaseLista')
                ->where('Id_Ejidatario', $ejidatario->Id_Ejidatario)
                ->where('Asistencia', 1)
                ->whereIn('Id_Sesion', $idsSesiones)
                ->pluck('Id_Sesion')
                ->toArray();

            $ejidatario->asistencias_asambleas = $sesionesAsambleas
                ->whereIn('Id_Sesion', $asistenciasEnSesion)
                ->pluck('Id_Referencia')
                ->toArray();
        }

        return view('cpanel.Descuentos.index', compact('ejidatarios', 'eventosAsambleas'));
    }
}