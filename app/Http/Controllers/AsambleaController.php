<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsambleaController extends Controller
{
    public function index(Request $request)
    {
        $anoActual = now()->year;

        // 1. Obtenemos solo eventos activos (sin fecha de eliminado)
        $eventosAsambleas = DB::table('Evento')
            ->where('Id_Categoria_Evento', 1)
            ->whereYear('Fecha_Creo', $anoActual)
            ->whereNull('Fecha_Eliminado') // <--- FILTRO MÁGICO: Excluye los eliminados
            ->get();

        // 2. Obtenemos las sesiones solo de los eventos que obtuvimos arriba
        $sesionesAsambleas = DB::table('Sesion')
            ->whereIn('Id_Referencia', $eventosAsambleas->pluck('Id_Evento'))
            ->where('Tipo', 'Evento')
            ->select('Id_Sesion', 'Id_Referencia')
            ->get();

        $idsSesiones = $sesionesAsambleas->pluck('Id_Sesion')->toArray();

        // 3. Consulta de ejidatarios
        $query = DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->select('e.Id_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno');

        if ($request->filled('query')) {
            $search = trim($request->get('query'));
            $query->where(function($q) use ($search) {
                $q->where('u.Nombres', 'LIKE', "%{$search}%")
                    ->orWhere('u.Apellido_Paterno', 'LIKE', "%{$search}%")
                    ->orWhere('u.Apellido_Materno', 'LIKE', "%{$search}%");
            });
        }

        $ejidatarios = $query->paginate(15)->withQueryString();

        // 4. Mapeo de asistencias eficiente
        // Obtenemos todas las asistencias de un solo golpe para no saturar la BD
        $todasLasAsistencias = DB::table('PaseLista')
            ->where('Asistencia', 1)
            ->whereIn('Id_Sesion', $idsSesiones)
            ->get();

        foreach ($ejidatarios as $ejidatario) {
            $idsSesionesAsistidas = $todasLasAsistencias
                ->where('Id_Ejidatario', $ejidatario->Id_Ejidatario)
                ->pluck('Id_Sesion');

            $ejidatario->asistencias_asambleas = $sesionesAsambleas
                ->whereIn('Id_Sesion', $idsSesionesAsistidas)
                ->pluck('Id_Referencia')
                ->toArray();
        }

        return view('cpanel.Descuentos.index', compact('ejidatarios', 'eventosAsambleas'));
    }
}