<?php

namespace App\Http\Controllers;

use App\Models\Utilidad;
use App\Models\Ejidatario;
use App\Models\Evento;
use App\Models\Descuento;
use App\Models\CatalogoMulta;
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

        $precios = CatalogoMulta::where('Año', $anoActual)->get();
        $costoAsamblea = $precios->where('Tipo', 'Asamblea')->first()->Costo ?? 0;
        $costoFaena = $precios->where('Tipo', 'Faena')->first()->Costo ?? 0;

        $eventosAsambleasIds = DB::table('Evento')
            ->where('Id_Categoria_Evento', 1)
            ->whereYear('Fecha_Creo', $anoActual)
            ->pluck('Id_Evento')->toArray();

        $eventosFaenasIds = DB::table('Evento')
            ->where('Id_Categoria_Evento', '!=', 1)
            ->whereYear('Fecha_Creo', $anoActual)
            ->pluck('Id_Evento')->toArray();

        $sesionesAsambleasIds = DB::table('Sesion')
            ->whereIn('Id_Referencia', $eventosAsambleasIds)
            ->where('Tipo', 'Evento')
            ->pluck('Id_Sesion')->toArray();

        $sesionesFaenasIds = DB::table('Sesion')
            ->whereIn('Id_Referencia', $eventosFaenasIds)
            ->where('Tipo', 'Evento')
            ->pluck('Id_Sesion')->toArray();

        $query = Ejidatario::with(['usuario', 'prestamos' => function($q) {
            $q->where('Id_Utilidad', $this->idUtilidadReparto1);
        }]);

        if ($request->filled('query')) {
            $search = $request->get('query');
            $query->whereHas('usuario', function($q) use ($search) {
                $q->where(function($sub) use ($search) {
                    $sub->where('Nombres', 'LIKE', "%$search%")
                        ->orWhere('Apellido_Paterno', 'LIKE', "%$search%")
                        ->orWhere('Apellido_Materno', 'LIKE', "%$search%")
                        ->orWhere(DB::raw("CONCAT(Nombres, ' ', Apellido_Paterno, ' ', Apellido_Materno)"), 'LIKE', "%$search%");
                });
            });
        }

        $ejidatarios = $query->paginate(15);

        $ejidatarios->getCollection()->transform(function ($ejidatario) use ($montoFijoR2, $sesionesAsambleasIds, $sesionesFaenasIds, $costoAsamblea, $costoFaena) {

            $ejidatario->deuda_arrastrada_r1 = $ejidatario->prestamos->sum('Cantidad') ?? 0;

            $asistenciasEjidatario = DB::table('PaseLista')
                ->where('Id_Ejidatario', $ejidatario->Id_Ejidatario)
                ->where('Asistencia', 1)
                ->pluck('Id_Sesion')
                ->toArray();

            $reprosAsambleas = DB::table('PaseLista')
                ->where('Id_Ejidatario', $ejidatario->Id_Ejidatario)
                ->where('Asistencia', 1)
                ->whereNull('Id_Sesion')
                ->where('Id_Actividad', 1)
                ->count();

            $reprosFaenas = DB::table('PaseLista')
                ->where('Id_Ejidatario', $ejidatario->Id_Ejidatario)
                ->where('Asistencia', 1)
                ->whereNull('Id_Sesion')
                ->where('Id_Actividad', 2)
                ->count();

            $faltasAsambleasCount = count(array_diff($sesionesAsambleasIds, $asistenciasEjidatario));
            $faltasFaenasCount = count(array_diff($sesionesFaenasIds, $asistenciasEjidatario));
            $ejidatario->total_asambleas = max(0, ($faltasAsambleasCount - $reprosAsambleas)) * $costoAsamblea;
            $ejidatario->total_faenas = max(0, ($faltasFaenasCount - $reprosFaenas)) * $costoFaena;

            $ejidatario->total_a_pagar = $montoFijoR2 - ($ejidatario->deuda_arrastrada_r1 + $ejidatario->total_asambleas + $ejidatario->total_faenas);

            return $ejidatario;
        });

        return view('cpanel.Repartos.segundo-reparto', compact('ejidatarios', 'montoFijoR2'));
    }

    public function obtenerDetalleAsambleas($id_ejidatario) {
        try {
            $anoActual = now()->year;
            $eventosIds = DB::table('Evento')->where('Id_Categoria_Evento', 1)->whereYear('Fecha_Creo', $anoActual)->pluck('Id_Evento');
            $sesionesIds = DB::table('Sesion')->whereIn('Id_Referencia', $eventosIds)->where('Tipo', 'Evento')->pluck('Id_Sesion')->toArray();

            $asistencias = DB::table('PaseLista')->where('Id_Ejidatario', $id_ejidatario)->whereIn('Id_Sesion', $sesionesIds)->pluck('Id_Sesion')->toArray();
            $faltasIds = array_diff($sesionesIds, $asistencias);

            $costoMulta = DB::table('Catalogo_Multa')->where('Año', $anoActual)->where('Tipo', 'Asamblea')->value('Costo') ?? 0;

            $detalles = DB::table('Sesion')
                ->join('Evento', 'Sesion.Id_Referencia', '=', 'Evento.Id_Evento')
                ->whereIn('Sesion.Id_Sesion', $faltasIds)
                ->select('Evento.Nombre_Evento as tipo')
                ->get()
                ->map(function($item) use ($costoMulta) {
                    return ['tipo' => $item->tipo, 'Descuento' => $costoMulta];
                });

            return response()->json($detalles);
        } catch (\Exception $e) { return response()->json(['error' => $e->getMessage()], 500); }
    }

    public function obtenerDetalleFaenas($id_ejidatario) {
        try {
            $anoActual = now()->year;
            $eventosIds = DB::table('Evento')->where('Id_Categoria_Evento', '!=', 1)->whereYear('Fecha_Creo', $anoActual)->pluck('Id_Evento');
            $sesionesIds = DB::table('Sesion')->whereIn('Id_Referencia', $eventosIds)->where('Tipo', 'Evento')->pluck('Id_Sesion')->toArray();

            $asistencias = DB::table('PaseLista')->where('Id_Ejidatario', $id_ejidatario)->whereIn('Id_Sesion', $sesionesIds)->pluck('Id_Sesion')->toArray();
            $faltasIds = array_diff($sesionesIds, $asistencias);

            $costoFaena = DB::table('Catalogo_Multa')->where('Año', $anoActual)->where('Tipo', 'Faena')->value('Costo') ?? 0;

            $detalles = DB::table('Sesion')
                ->join('Evento', 'Sesion.Id_Referencia', '=', 'Evento.Id_Evento')
                ->whereIn('Sesion.Id_Sesion', $faltasIds)
                ->select('Evento.Nombre_Evento as tipo')
                ->get()
                ->map(function($item) use ($costoFaena) {
                    return ['tipo' => $item->tipo, 'Descuento' => $costoFaena];
                });

            return response()->json($detalles);
        } catch (\Exception $e) { return response()->json(['error' => $e->getMessage()], 500); }
    }

    public function abonarPrestamo(Request $request, $id)
    {
        try {
            $prestamo = DB::table('Prestamos')->where('Id_Prestamo', $id)->first();
            if ($prestamo) {
                DB::table('Abonos')->insert([
                    'Id_Prestamo' => $id,
                    'Cantidad'    => $request->monto,
                    'Fecha_Creo'  => now(),
                    'Id_Creo'     => session('usuario.nombre') ?? 'Sistema'
                ]);
                DB::table('Prestamos')->where('Id_Prestamo', $id)
                    ->update(['Cantidad' => DB::raw("Cantidad - " . $request->monto)]);
                return redirect()->back()->with('success', 'Pago realizado con éxito');
            }
            return redirect()->back()->with('error', 'No se encontró el préstamo');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function reprogramarFalta(Request $request) {
        try {
            if (strtotime($request->fecha_nueva) < strtotime(date('Y-m-d'))) {
                return response()->json(['success' => false, 'message' => 'Fecha inválida.']);
            }
            $evento = DB::table('Evento')->where('Nombre_Evento', $request->tipo_evento)->first();
            $idActividad = ($evento && $evento->Id_Categoria_Evento == 1) ? 1 : 2;

            DB::table('PaseLista')->insert([
                'Asistencia'    => 1,
                'Fecha'         => $request->fecha_nueva,
                'Id_Ejidatario' => $request->id_ejidatario,
                'Id_Sesion'     => null,
                'Id_Actividad'  => $idActividad
            ]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) { return response()->json(['success' => false, 'message' => $e->getMessage()], 500); }
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