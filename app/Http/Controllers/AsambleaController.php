<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsambleaController extends Controller
{
    public function index(Request $request)
    {
        $eventosAsambleas = DB::table('Evento')
            ->where('Id_Categoria_Evento', 1)
            ->orderBy('Fecha_Creo', 'DESC')
            ->get();

        $idsEventos = $eventosAsambleas->pluck('Id_Evento')->toArray();

        $sesionesAsambleas = DB::table('Sesion')
            ->whereIn('Id_Referencia', $idsEventos)
            ->where('Tipo', 'Evento')
            ->get();

        $query = DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->select('e.Id_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno');

        if ($request->filled('query')) {
            $search = $request->query('query');
            $query->where(function($q) use ($search) {
                $q->where('u.Nombres', 'LIKE', "%{$search}%")
                    ->orWhere('u.Apellido_Paterno', 'LIKE', "%{$search}%")
                    ->orWhere('u.Apellido_Materno', 'LIKE', "%{$search}%");
            });
        }

        $ejidatarios = $query->orderBy('u.Apellido_Paterno')->paginate(15)->withQueryString();

        $asistencias = DB::table('PaseLista')
            ->where('Asistencia', 1)
            ->whereIn('Id_Sesion', $sesionesAsambleas->pluck('Id_Sesion'))
            ->get();

        foreach ($ejidatarios as $ejidatario) {
            $idsSesionesAsistidas = $asistencias->where('Id_Ejidatario', $ejidatario->Id_Ejidatario)->pluck('Id_Sesion');

            $ejidatario->asistencias_asambleas = $sesionesAsambleas
                ->whereIn('Id_Sesion', $idsSesionesAsistidas)
                ->pluck('Id_Referencia')
                ->toArray();
        }

        return view('cpanel.Descuentos.index', compact('ejidatarios', 'eventosAsambleas'));
    }
}