<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ejidatario;
use App\Models\Usuario;
use App\Models\Descuento;
use App\Models\CatalogoMulta;
use Illuminate\Support\Facades\DB;

class FaenasController extends Controller
{
    public function index(Request $request)
    {
        $anoActual = now()->year;

        $eventosFaenas = DB::table('Evento')
            ->whereIn('Id_Categoria_Evento', [9, 10])
            ->whereYear('Fecha_Creo', $anoActual)
            ->whereNull('Fecha_Eliminado')
            ->get();

        $sesionesFaenas = DB::table('Sesion')
            ->whereIn('Id_Referencia', $eventosFaenas->pluck('Id_Evento'))
            ->where('Tipo', 'Evento')
            ->select('Id_Sesion', 'Id_Referencia')
            ->get();

        $idsSesiones = $sesionesFaenas->pluck('Id_Sesion')->toArray();

        $query = Ejidatario::with(['usuario', 'descuentos']);

        if ($request->filled('query')) {
            $search = $request->get('query');
            $query->whereHas('usuario', function($q) use ($search) {
                $q->where('Nombres', 'LIKE', "%$search%")
                    ->orWhere('Apellido_Paterno', 'LIKE', "%$search%")
                    ->orWhere('Apellido_Materno', 'LIKE', "%$search%");
            });
        }

        $ejidatarios = $query->paginate(10);

        foreach ($ejidatarios as $ejidatario) {
            $asistenciasEnSesion = DB::table('PaseLista')
                ->where('Id_Ejidatario', $ejidatario->Id_Ejidatario)
                ->where('Asistencia', 1)
                ->whereIn('Id_Sesion', $idsSesiones)
                ->pluck('Id_Sesion')
                ->toArray();

            $ejidatario->asistencias_confirmadas = $sesionesFaenas
                ->whereIn('Id_Sesion', $asistenciasEnSesion)
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
        $usuarios = Usuario::whereHas('ejidatario')
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