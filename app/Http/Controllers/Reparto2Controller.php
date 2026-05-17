<?php

namespace App\Http\Controllers;

use App\Models\Utilidad;
use App\Models\Ejidatario;
use App\Models\Evento;
use App\Models\Descuento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Reparto2Controller extends Controller
{
    private $idUtilidadReparto1 = 1;
    private $idUtilidadReparto2 = 2;

    public function mostrarSegundoReparto(Request $request)
    {
        $reparto2 = Utilidad::find($this->idUtilidadReparto2);
        $montoFijoR2 = $reparto2 ? $reparto2->Monto : 0;
        $anoActual = now()->year;

        $eventosAsambleas = DB::table('Evento')->where('Id_Categoria_Evento', 1)->whereYear('Fecha_Creo', $anoActual)->pluck('Id_Evento')->toArray();
        $eventosFaenas = DB::table('Evento')->whereIn('Id_Categoria_Evento', [2, 3])->whereYear('Fecha_Creo', $anoActual)->pluck('Id_Evento')->toArray();

        $query = Ejidatario::with(['usuario', 'descuentos', 'prestamos' => function($q) {
            $q->where('Id_Utilidad', $this->idUtilidadReparto1);
        }]);

        if ($request->filled('query')) {
            $search = $request->get('query');
            $query->whereHas('usuario', function($q) use ($search) {
                $q->where('Nombres', 'LIKE', "%$search%")
                    ->orWhere('Apellido_Paterno', 'LIKE', "%$search%");
            });
        }

        $ejidatarios = $query->paginate(15);

        $ejidatarios->getCollection()->transform(function ($ejidatario) use ($montoFijoR2, $eventosAsambleas, $eventosFaenas) {

            $ejidatario->deuda_arrastrada_r1 = $ejidatario->prestamos->sum('Cantidad') ?? 0;
            $asistenciasUser = DB::table('PaseLista')
                ->where('Id_Ejidatario', $ejidatario->Id_Ejidatario)
                ->where('Asistencia', 1)
                ->pluck('Id_Sesion')
                ->toArray();

            $ejidatario->total_asambleas = $ejidatario->descuentos
                ->whereIn('Id_MultaC', $eventosAsambleas)
                ->sum('Descuento') ?? 0;

            $ejidatario->total_faenas = $ejidatario->descuentos
                ->whereIn('Id_MultaC', $eventosFaenas)
                ->sum('Descuento') ?? 0;

            // 4. Cálculo final
            $ejidatario->total_a_pagar = $montoFijoR2 - ($ejidatario->deuda_arrastrada_r1 + $ejidatario->total_asambleas + $ejidatario->total_faenas);

            return $ejidatario;
        });

        return view('cpanel.Repartos.segundo-reparto', compact('ejidatarios', 'montoFijoR2'));
    }


    public function obtenerDetalleAsambleas($id_ejidatario) {
        $detalles = Descuento::where('Id_Ejidatario', $id_ejidatario)
            ->where('tipo', 'LIKE', '%ASAMBLEA%')
            ->get();

        return response()->json($detalles);
    }


    public function obtenerDetalleFaenas($id_ejidatario) {
        $detalles = Descuento::where('Id_Ejidatario', $id_ejidatario)
            ->where(function($q) {
                $q->where('tipo', 'LIKE', '%saneamient%')
                    ->orWhere('tipo', 'LIKE', '%aprovecham%');
            })
            ->get();

        return response()->json($detalles);
    }


    public function perdonarAsamblea($id) {
        try {
            $descuento = Descuento::findOrFail($id);
            $descuento->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function fijarFechaLimite(Request $request) {
        $request->validate(['fecha_limite' => 'required|date']);
        $utilidad = Utilidad::find($this->idUtilidadReparto2);
        if ($utilidad) {
            $utilidad->Fecha_Eliminado = $request->fecha_limite;
            $utilidad->save();
            return response()->json(['success' => true, 'message' => 'Fecha actualizada']);
        }
        return response()->json(['success' => false], 404);
    }

    public function obtenerFechaLimite() {
        $utilidad = Utilidad::find($this->idUtilidadReparto2);
        return response()->json(['fecha_limite' => $utilidad?->Fecha_Eliminado ?? '']);
    }


    public function store(Request $request)
    {
        try {
            $multa = CatalogoMulta::findOrFail($request->id_multa_c);

            $id = DB::table('Descuentos')->insertGetId([
                'Id_Ejidatario' => $request->id_ejidatario,
                'Id_MultaC'     => $request->id_multa_c,
                'tipo'          => trim($multa->Tipo),
                'Descuento'     => $multa->Costo,
                'Id_Creo'       => session('usuario.nombre') ?? 'Sistema',
                'Fecha_Creo'    => now()->format('Y-m-d')
            ]);

            return response()->json(['success' => true, 'id' => $id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}