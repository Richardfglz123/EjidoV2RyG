<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ejidatario;
use App\Models\Usuario;
use App\Models\Descuento;
use App\Models\CatalogoMulta;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FaenasController extends Controller
{
    public function index(Request $request)
    {
        $anoActual = \Carbon\Carbon::now()->year;

        // 1. Obtener eventos de faena (Categorías 9 y 10)
        $eventosFaenas = \DB::table('Evento')
            ->whereIn('Id_Categoria_Evento', [9, 10])
            ->whereYear('Fecha_Creo', $anoActual)
            ->whereNull('Fecha_Eliminado')
            ->get();

        // 2. Obtener ejidatarios
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

        // 3. Mapear asistencias POR CADA EJIDATARIO
// En FaenasController.php, dentro del foreach de ejidatarios:
        foreach ($ejidatarios as $ejidatario) {
            // Buscamos asistencias donde el Id_Sesion coincida con el Id_Evento
            // O donde la Sesión esté vinculada al evento por Id_Referencia
            $ejidatario->asistencias_confirmadas = DB::table('PaseLista')
                ->leftJoin('Sesion', 'PaseLista.Id_Sesion', '=', 'Sesion.Id_Sesion')
                ->where('PaseLista.Id_Ejidatario', $ejidatario->Id_Ejidatario)
                ->where('PaseLista.Asistencia', 1)
                ->where(function($q) use ($eventosFaenas) {
                    $ids = $eventosFaenas->pluck('Id_Evento');
                    $q->whereIn('PaseLista.Id_Sesion', $ids) // Casos nuevos
                    ->orWhereIn('Sesion.Id_Referencia', $ids); // Casos viejos (7, 8 vinculados a 11, 12)
                })
                ->pluck('PaseLista.Id_Sesion', 'Sesion.Id_Referencia')
                ->map(function($val, $key) {
                    return $key ?: $val; // Retorna el ID del evento sin importar la columna
                })
                ->toArray();
        }

        return view('cpanel.Descuentos.faenas', compact('ejidatarios', 'eventosFaenas'));
    }

    public function aplicarDescuento(Request $request)
    {
        // 🔥 CORRECCIÓN: Ajustados los nombres de tablas/columnas a la estructura Real (Id_Ejidatario, Catalogo_Multa)
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
            $montoDescuento = $multa->Costo ?? $multa->monto; // Protegido por si es Costo o monto

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

        // 🔥 CORRECCIÓN: Cambiado 'nombre' por 'Nombres', 'apellido_paterno' por 'Apellido_Paterno', etc.
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