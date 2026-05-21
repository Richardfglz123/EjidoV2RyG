<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsambleaController extends Controller
{
    public function index(Request $request)
    {
        // 1. Obtenemos TODOS los eventos que tienen al menos una sesión de tipo 'Evento'
        // Esto evita filtrar por categorías específicas que te estaban ocultando eventos
        $eventosAsambleas = DB::table('Evento')
            ->join('Sesion', 'Evento.Id_Evento', '=', 'Sesion.Id_Referencia')
            ->where('Sesion.Tipo', 'Evento')
            ->select('Evento.*')
            ->distinct()
            ->orderBy('Evento.Fecha_Creo', 'DESC')
            ->get();

        $idsEventos = $eventosAsambleas->pluck('Id_Evento')->toArray();

        // 2. Obtenemos las sesiones vinculadas a esos eventos
        $sesionesAsambleas = DB::table('Sesion')
            ->whereIn('Id_Referencia', $idsEventos)
            ->where('Tipo', 'Evento')
            ->select('Id_Sesion', 'Id_Referencia')
            ->get();

        $idsSesiones = $sesionesAsambleas->pluck('Id_Sesion')->toArray();

        // 3. Consulta de Ejidatarios con Buscador
        $query = DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->select(
                'e.Id_Ejidatario',
                'u.Nombres',
                'u.Apellido_Paterno',
                'u.Apellido_Materno'
            );

        // Aplicar filtro de búsqueda si existe
        if ($request->filled('query')) {
            $search = trim($request->get('query'));
            $query->where(function($q) use ($search) {
                $q->where('u.Nombres', 'LIKE', "%{$search}%")
                    ->orWhere('u.Apellido_Paterno', 'LIKE', "%{$search}%")
                    ->orWhere('u.Apellido_Materno', 'LIKE', "%{$search}%");
            });
        }

        // Paginamos y mantenemos el parámetro de búsqueda en los enlaces
        $ejidatarios = $query->orderBy('u.Apellido_Paterno')
            ->paginate(15)
            ->withQueryString();

        // 4. Mapeo de asistencias eficiente
        // Traemos todas las asistencias de una vez para no saturar la base de datos
        $asistencias = DB::table('PaseLista')
            ->where('Asistencia', 1)
            ->whereIn('Id_Sesion', $idsSesiones)
            ->select('Id_Ejidatario', 'Id_Sesion')
            ->get();

        foreach ($ejidatarios as $ejidatario) {
            // Buscamos qué sesiones asistió este ejidatario en particular
            $sesionesAsistidas = $asistencias->where('Id_Ejidatario', $ejidatario->Id_Ejidatario)
                ->pluck('Id_Sesion');

            // Asignamos los IDs de los eventos a los que asistió
            $ejidatario->asistencias_asambleas = $sesionesAsambleas
                ->whereIn('Id_Sesion', $sesionesAsistidas)
                ->pluck('Id_Referencia')
                ->toArray();
        }

        return view('cpanel.Descuentos.index', compact('ejidatarios', 'eventosAsambleas'));
    }
}