<?php

namespace App\Http\Controllers;

use App\Models\Utilidad;
use App\Models\Ejidatario;
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

        // Precios de multas
        $precios = CatalogoMulta::where('Año', $anoActual)->get();
        $costoAsamblea = $precios->where('Tipo', 'Asamblea')->first()->Costo ?? 0;
        $costoFaena = $precios->where('Tipo', 'Faena')->first()->Costo ?? 0;

        // Ids de Sesiones para cálculos
        $sesionesAsambleasIds = DB::table('Sesion')
            ->join('Evento', 'Sesion.Id_Referencia', '=', 'Evento.Id_Evento')
            ->where('Evento.Id_Categoria_Evento', 1)
            ->whereYear('Evento.Fecha_Creo', $anoActual)
            ->where('Sesion.Tipo', 'Evento')
            ->pluck('Sesion.Id_Sesion')->toArray();

        $sesionesFaenasIds = DB::table('Sesion')
            ->join('Evento', 'Sesion.Id_Referencia', '=', 'Evento.Id_Evento')
            ->where('Evento.Id_Categoria_Evento', '!=', 1)
            ->whereYear('Evento.Fecha_Creo', $anoActual)
            ->where('Sesion.Tipo', 'Evento')
            ->pluck('Sesion.Id_Sesion')->toArray();

        // CONSULTA CON JOIN PARA ASEGURAR NOMBRES
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
                    ->orWhere('u.Apellido_Materno', 'LIKE', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT(u.Nombres, ' ', u.Apellido_Paterno, ' ', u.Apellido_Materno)"), 'LIKE', "%{$search}%");
            });
        }

        $ejidatarios = $query->paginate(15);

        $ejidatarios->getCollection()->transform(function ($ejidatario) use ($montoFijoR2, $sesionesAsambleasIds, $sesionesFaenasIds, $costoAsamblea, $costoFaena) {

            $totalPrestamoR1 = DB::table('Prestamo')
                ->where('Id_Ejidatario', $ejidatario->Id_Ejidatario)
                ->where('Id_Utilidad', $this->idUtilidadReparto1)
                // ->where('Estatus', '!=', 'Siguiente Año') // <- Descomenta si agregas la columna
                ->sum('Cantidad') ?? 0;

            $totalAbonosR1 = DB::table('Abono')
                ->join('Prestamo', 'Abono.Id_Prestamo', '=', 'Prestamo.Id_Prestamo')
                ->where('Prestamo.Id_Ejidatario', $ejidatario->Id_Ejidatario)
                ->where('Prestamo.Id_Utilidad', $this->idUtilidadReparto1)
                ->sum('Abono.Monto') ?? 0; // Nota: En tu controller usas 'Monto' en un lado y 'Cantidad' en otro, verifica cuál es el campo real en Abonos.

            $deudaRealRestante = max(0, $totalPrestamoR1 - $totalAbonosR1);

            $ejidatario->deuda_arrastrada_r1 = $deudaRealRestante;

            $asistenciasEjidatario = DB::table('PaseLista')
                ->where('Id_Ejidatario', $ejidatario->Id_Ejidatario)
                ->where('Asistencia', 1)
                ->whereNotNull('Id_Sesion')
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
    public function posponerSiguienteAnio($id)
    {
        try {
            // Opción segura: Buscamos los préstamos del R1 de este ejidatario y los desvinculamos del flujo actual
            // cambiando su Id_Utilidad a un estado de resguardo (ej. 99) o actualizando una columna Estatus
            $actualizados = DB::table('Prestamo')
                ->where('Id_Ejidatario', $id)
                ->where('Id_Utilidad', $this->idUtilidadReparto1)
                ->update([
                    'Id_Utilidad' => 99 // O la lógica/columna que decidas para "Siguiente Año"
                ]);

            if ($actualizados > 0) {
                return redirect()->back()->with('success', 'La deuda pendiente se ha congelado y trasladado al siguiente año.');
            }
            return redirect()->back()->with('error', 'No se encontraron préstamos pendientes para trasladar.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al reprogramar: ' . $e->getMessage());
        }
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
        $request->validate(['monto' => 'required|numeric|min:0.01']);
        try {
            $prestamo = DB::table('Prestamo')
                ->where('Id_Ejidatario', $id)
                ->where('Id_Utilidad', $this->idUtilidadReparto1)
                ->first();

            if ($prestamo) {
                // Insertar historial de abonos
                DB::table('Abono')->insert([
                    'Id_Prestamo' => $prestamo->Id_Prestamo,
                    'Monto'       => $request->monto,
                    'Fecha'       => now()
                ]);

                return redirect()->back()->with('success', 'Abono registrado con éxito en el Préstamo del R1.');
            }

            return redirect()->back()->with('error', 'El ejidatario no tiene un préstamo activo en el Primer Reparto para abonar.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al procesar el pago: ' . $e->getMessage());
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
    public function generarTicketPDFSegundo($id)
    {
        $ejidatario = DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->where('e.Id_Ejidatario', $id)
            ->select('e.Id_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno')
            ->first();

        if (!$ejidatario) { return abort(404); }

        $reparto2 = Utilidad::find($this->idUtilidadReparto2);
        $montoFijoR2 = $reparto2 ? $reparto2->Monto : 0;
        $anoActual = now()->year;

        $precios = CatalogoMulta::where('Año', $anoActual)->get();
        $costoAsamblea = $precios->where('Tipo', 'Asamblea')->first()->Costo ?? 0;
        $costoFaena = $precios->where('Tipo', 'Faena')->first()->Costo ?? 0;

        $sesionesAsambleasIds = DB::table('Sesion')
            ->join('Evento', 'Sesion.Id_Referencia', '=', 'Evento.Id_Evento')
            ->where('Evento.Id_Categoria_Evento', 1)
            ->whereYear('Evento.Fecha_Creo', $anoActual)
            ->where('Sesion.Tipo', 'Evento')
            ->pluck('Sesion.Id_Sesion')->toArray();

        $sesionesFaenasIds = DB::table('Sesion')
            ->join('Evento', 'Sesion.Id_Referencia', '=', 'Evento.Id_Evento')
            ->where('Evento.Id_Categoria_Evento', '!=', 1)
            ->whereYear('Evento.Fecha_Creo', $anoActual)
            ->where('Sesion.Tipo', 'Evento')
            ->pluck('Sesion.Id_Sesion')->toArray();

        $asistenciasEjidatario = DB::table('PaseLista')
            ->where('Id_Ejidatario', $id)
            ->where('Asistencia', 1)
            ->whereNotNull('Id_Sesion')
            ->pluck('Id_Sesion')
            ->toArray();

        $reprosAsambleas = DB::table('PaseLista')
            ->where('Id_Ejidatario', $id)
            ->where('Asistencia', 1)
            ->whereNull('Id_Sesion')
            ->where('Id_Actividad', 1)
            ->count();

        $reprosFaenas = DB::table('PaseLista')
            ->where('Id_Ejidatario', $id)
            ->where('Asistencia', 1)
            ->whereNull('Id_Sesion')
            ->where('Id_Actividad', 2)
            ->count();

        $faltasAsambleasCount = count(array_diff($sesionesAsambleasIds, $asistenciasEjidatario));
        $faltasFaenasCount = count(array_diff($sesionesFaenasIds, $asistenciasEjidatario));

        $totalAsambleas = max(0, ($faltasAsambleasCount - $reprosAsambleas)) * $costoAsamblea;
        $totalFaenas = max(0, ($faltasFaenasCount - $reprosFaenas)) * $costoFaena;

        $totalPrestamoR1 = DB::table('Prestamo')
            ->where('Id_Ejidatario', $id)
            ->where('Id_Utilidad', $this->idUtilidadReparto1)
            ->sum('Cantidad') ?? 0;

        $totalAbonosR1 = DB::table('Abono')
            ->join('Prestamo', 'Abono.Id_Prestamo', '=', 'Prestamo.Id_Prestamo')
            ->where('Prestamo.Id_Ejidatario', $id)
            ->where('Prestamo.Id_Utilidad', $this->idUtilidadReparto1)
            ->sum('Abono.Monto') ?? 0;

        $deudaArrastrada = max(0, $totalPrestamoR1 - $totalAbonosR1);

        $idPrestamos = DB::table('Prestamo')
            ->where('Id_Ejidatario', $id)
            ->where('Id_Utilidad', $this->idUtilidadReparto1)
            ->pluck('Id_Prestamo');

        $historialAbonos = DB::table('Abono')
            ->whereIn('Id_Prestamo', $idPrestamos)
            ->orderBy('Fecha', 'asc')
            ->get();

        $totalDeducciones = $totalAsambleas + $totalFaenas + $deudaArrastrada;
        $totalAPagar = $montoFijoR2 - $totalDeducciones;

        return \PDF::loadView('cpanel.Repartos.ticket-general-reparto2', [
            'ejidatario'      => $ejidatario,
            'montoFijoR2'     => $montoFijoR2,
            'totalAsambleas'  => $totalAsambleas,
            'totalFaenas'     => $totalFaenas,
            'deudaArrastrada' => $deudaArrastrada,
            'historialAbonos' => $historialAbonos,
            'totalAbonosR1'   => $totalAbonosR1,
            'totalPrestamoR1' => $totalPrestamoR1,
            'totalDeducciones'=> $totalDeducciones,
            'totalAPagar'     => $totalAPagar
        ])->stream('ticket-segundo-reparto-'.$id.'.pdf');
    }
}