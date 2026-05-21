<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Descuento; // Asegúrate de importar tus modelos
use App\Models\CatalogoMulta;
use App\Models\usuario;

class FaenasController extends Controller
{
    public function index(Request $request)
    {
        $eventosFaenas = DB::table('Evento')
            ->join('Sesion', 'Evento.Id_Evento', '=', 'Sesion.Id_Referencia')
            ->where('Sesion.Tipo', 'Evento')
            ->whereNull('Evento.Fecha_Eliminado')
            ->select('Evento.*')
            ->distinct()
            ->orderBy('Evento.Fecha_Creo', 'DESC')
            ->get();

        $idsEventos = $eventosFaenas->pluck('Id_Evento')->toArray();

        $sesionesFaenas = DB::table('Sesion')
            ->whereIn('Id_Referencia', $idsEventos)
            ->where('Tipo', 'Evento')
            ->select('Id_Sesion', 'Id_Referencia')
            ->get();

        $idsSesiones = $sesionesFaenas->pluck('Id_Sesion')->toArray();

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

        $ejidatarios = $query->orderBy('u.Apellido_Paterno')
            ->paginate(15)
            ->withQueryString();

        $asistencias = DB::table('PaseLista')
            ->where('Asistencia', 1)
            ->whereIn('Id_Sesion', $idsSesiones)
            ->get();

        foreach ($ejidatarios as $ejidatario) {
            $sesionesAsistidas = $asistencias->where('Id_Ejidatario', $ejidatario->Id_Ejidatario)
                ->pluck('Id_Sesion');

            $ejidatario->asistencias_confirmadas = $sesionesFaenas
                ->whereIn('Id_Sesion', $sesionesAsistidas)
                ->pluck('Id_Referencia')
                ->toArray();
        }

        return view('cpanel.Descuentos.faenas', compact('ejidatarios', 'eventosFaenas'));
    }


    public function aplicarDescuento(Request $request)
    {
        $request->validate([
            'id_ejidatario' => 'required|integer',
            'nombre_faena' => 'required|string|max:100',
            'id_multa_c' => 'nullable|integer'
        ]);

        $id_ejidatario = $request->id_ejidatario;
        $nombre_faena = $request->nombre_faena;
        $id_multa = $request->id_multa_c;

        if ($id_multa) {
            $multa = CatalogoMulta::find($id_multa);
            $montoDescuento = $multa->Costo ?? $multa->monto;

            Descuento::updateOrCreate(
                [
                    'Id_Ejidatario' => $id_ejidatario,
                    'tipo' => $nombre_faena
                ],
                [
                    'descuento' => $montoDescuento
                ]
            );
        } else {
            Descuento::where('Id_Ejidatario', $id_ejidatario)
                ->where('tipo', $nombre_faena)
                ->delete();
        }

        return response()->json(['success' => true, 'message' => 'Descuento actualizado.']);
    }

    public function buscarEjidatarios(Request $request)
    {
        $query = $request->get('query');
        if (empty($query)) {
            return response()->json([]);
        }
        $usuarios = usuario::whereHas('ejidatario')
            ->where(function($q) use ($query) {
                $q->where('Nombres', 'LIKE', '%' . $query . '%')
                    ->orWhere('Apellido_Paterno', 'LIKE', '%' . $query . '%')
                    ->orWhere('Apellido_Materno', 'LIKE', '%' . $query . '%');
            })
            ->with('ejidatario')
            ->limit(5)
            ->get();

        return response()->json($usuarios);
    }
}