<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsambleaController extends Controller
{
    public function index(Request $request)
    {
        // 1. Obtener eventos que NO han sido eliminados
        $eventosAsambleas = DB::table('Evento')
            ->join('Sesion', 'Evento.Id_Evento', '=', 'Sesion.Id_Referencia')
            ->where('Sesion.Tipo', 'Evento')
            ->whereNull('Evento.Fecha_Eliminado') // Filtro aplicado aquí
            ->select('Evento.*')
            ->distinct()
            ->orderBy('Evento.Fecha_Creo', 'DESC')
            ->get();

        $idsEventos = $eventosAsambleas->pluck('Id_Evento')->toArray();

        // 2. Obtener sesiones de eventos que NO han sido eliminados
        $sesionesAsambleas = DB::table('Sesion')
            ->join('Evento', 'Sesion.Id_Referencia', '=', 'Evento.Id_Evento')
            ->whereIn('Sesion.Id_Referencia', $idsEventos)
            ->where('Sesion.Tipo', 'Evento')
            ->whereNull('Evento.Fecha_Eliminado') // Filtro aplicado aquí también
            ->select('Sesion.Id_Sesion', 'Sesion.Id_Referencia')
            ->get();

        $idsSesiones = $sesionesAsambleas->pluck('Id_Sesion')->toArray();

        // 3. Consulta de ejidatarios
        $query = DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->select(
                'e.Id_Ejidatario',
                'u.Nombres',
                'u.Apellido_Paterno',
                'u.Apellido_Materno'
            );

        if ($request->filled('query')) {
            $search = trim($request->get('query'));
            $query->where(function($q) use ($search) {
                $q->where('u.Nombres', 'LIKE', "%{$search}%")
                    ->orWhere('u.Apellido_Paterno', 'LIKE', "%{$search}%")
                    ->orWhere('u.Apellido_Materno', 'LIKE', "%{$search}%");
            });
        }

        $ejidatarios = $query->orderBy('u.Apellido_Paterno')
            ->paginate(15)
            ->withQueryString();

        // 4. Obtener asistencias filtrando solo por las sesiones válidas
        $asistencias = DB::table('PaseLista')
            ->where('Asistencia', 1)
            ->whereIn('Id_Sesion', $idsSesiones)
            ->select('Id_Ejidatario', 'Id_Sesion')
            ->get();

        // 5. Asignar asistencias a cada ejidatario
        foreach ($ejidatarios as $ejidatario) {
            $sesionesAsistidas = $asistencias->where('Id_Ejidatario', $ejidatario->Id_Ejidatario)
                ->pluck('Id_Sesion');

            $ejidatario->asistencias_asambleas = $sesionesAsambleas
                ->whereIn('Id_Sesion', $sesionesAsistidas)
                ->pluck('Id_Referencia')
                ->toArray();
        }

        return view('cpanel.Descuentos.index', compact('ejidatarios', 'eventosAsambleas'));
    }
}