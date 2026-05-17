<?php

namespace App\Http\Controllers;

use App\Models\Ejidatario;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsambleaController extends Controller
{
    public function index(Request $request)
    {
        $anoActual = now()->year;

        $eventosAsambleas = DB::table('Evento')
            ->where('Id_Categoria_Evento', 1)
            ->whereYear('Fecha_Creo', $anoActual)
            ->get();

        $sesionesAsambleas = DB::table('Sesion')
            ->whereIn('Id_Referencia', $eventosAsambleas->pluck('Id_Evento'))
            ->where('Tipo', 'Evento')
            ->select('Id_Sesion', 'Id_Referencia')
            ->get();

        $idsSesiones = $sesionesAsambleas->pluck('Id_Sesion')->toArray();

        $query = Ejidatario::with(['usuario']);

        if ($request->filled('query')) {
            $search = $request->get('query');
            $query->whereHas('usuario', function($q) use ($search) {
                $q->where('Nombres', 'LIKE', "%$search%")
                    ->orWhere('Apellido_Paterno', 'LIKE', "%$search%");
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