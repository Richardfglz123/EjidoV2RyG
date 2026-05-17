<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestamo;
use App\Models\Utilidad;
use App\Models\usuario;
use App\Models\Ejidatario;
use App\Models\CatalogoMulta;
use App\Models\Abono;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RepartoController extends Controller
{
    public function menu()
    {
        $data = [
            'finiquito_saneamiento'     => Utilidad::where('Tipo_Reparto', 'REPARTO FINIQUITO')->first(),
            'primer_reparto'            => Utilidad::where('Tipo_Reparto', 'PRIMER REPARTO')->first(),
            'segundo_reparto'           => Utilidad::where('Tipo_Reparto', 'SEGUNDO REPARTO')->first(),
            'finiquito_utilidades'      => Utilidad::where('Tipo_Reparto', 'FINIQUITO UTILIDADES')->first(),

            'descuento_saneamiento'     => CatalogoMulta::where('tipo', 'SANEAMIENTO')->first(),
            'descuento_aprovechamiento' => CatalogoMulta::where('tipo', 'APROVECHAMIENTO')->first(),
            'descuento_asambleas'       => CatalogoMulta::where('tipo', 'ASAMBLEAS')->first(),
        ];

        return view('cpanel.monto.menu', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0',
            'anio' => 'required|numeric',
            'responsable' => 'required',
        ]);

        $utilidad = Utilidad::where('Id_Utilidad', $id)->firstOrFail();

        $utilidad->Monto = $request->monto;
        $utilidad->Año = $request->anio;
        if($request->has('nombre_reparto')){
            $utilidad->Tipo_Reparto = $request->nombre_reparto;
        }

        $utilidad->Id_Modificado = $request->responsable;
        $utilidad->Fecha_Modificado = now();
        $utilidad->save();

        return redirect()->route('menu')->with('success', 'Monto actualizado correctamente.');
    }

    public function index(Request $request)
    {
        $utilidades = Utilidad::all();
        $usuarios = usuario::all();
        $idSeleccionado = $request->input('id_utilidad');
        $utilidadSeleccionada = $idSeleccionado ? Utilidad::find($idSeleccionado) : null;

        return view('cpanel.monto.monto', compact('utilidades', 'utilidadSeleccionada', 'usuarios'));
    }

    public function mostrarPrimerReparto()
    {
        $idPrimerReparto = 1;
        $reparto1 = Utilidad::find($idPrimerReparto);
        $montoReparto1 = $reparto1?->Monto ?? 0;

        $fechaLimite = $reparto1?->Fecha_Eliminado;

        $deadlinePasada = $fechaLimite
            ? Carbon::now()->gt(Carbon::parse($fechaLimite)->endOfDay())
            : false;

        $prestamos = Prestamo::where('Id_Utilidad', $idPrimerReparto)
            ->with('ejidatario.usuario')
            ->withSum('abonos as total_abonado', 'Monto') // <-- Crucial: añade esto
            ->paginate(10);

        return view('cpanel.Repartos.primer-reparto', compact(
            'prestamos', 'montoReparto1', 'deadlinePasada', 'reparto1'
        ));
    }

    public function mostrarSegundoReparto()
    {
        $primerReparto = Utilidad::where('Tipo_Reparto', 'primer_reparto')->first();
        $segundoReparto = Utilidad::where('Tipo_Reparto', 'segundo_reparto')->first();

        $fechaLimite = $primerReparto?->Fecha_Eliminado;

        $puedeManipularSegundo = false;
        if ($fechaLimite) {
            $puedeManipularSegundo = Carbon::now()->gt(Carbon::parse($fechaLimite)->endOfDay());
        }

        $prestamos = Prestamo::where('Id_Utilidad', $segundoReparto->Id_Utilidad)
            ->with('ejidatario.usuario')
            ->paginate(10);

        return view('cpanel.repartos.segundo-reparto', compact(
            'prestamos',
            'segundoReparto',
            'puedeManipularSegundo'
        ));
    }

    public function agregarPrestamo(Request $request)
    {
        $request->validate([
            'id_ejidatario' => 'required|exists:Ejidatario,Id_Ejidatario',
            'motivo' => 'required|string|max:255',
            'cantidad' => 'required|numeric|min:1',
        ]);

        Prestamo::create([
            'Id_Ejidatario' => $request->id_ejidatario,
            'Motivo'        => $request->motivo,
            'Cantidad'      => $request->cantidad,
            'Fecha'         => now(),
            'Id_Utilidad'   => 1,
        ]);

        return redirect()->back()->with('success', 'Préstamo registrado correctamente.');
    }

    public function buscarEjidatario(Request $request)
    {
        $q = $request->q;
        $ejidatarios = Ejidatario::with('usuario')
            ->whereHas('usuario', function ($query) use ($q) {
                $query->where('Nombres', 'LIKE', "%{$q}%")
                    ->orWhere('Apellido_Paterno', 'LIKE', "%{$q}%")
                    ->orWhere('Apellido_Materno', 'LIKE', "%{$q}%");
            })->get();

        return response()->json(
            $ejidatarios->map(function ($e) {
                return [
                    'id' => $e->Id_Ejidatario,
                    'text' => $e->usuario->Nombres . ' ' . $e->usuario->Apellido_Paterno . ' ' . $e->usuario->Apellido_Materno
                ];
            })
        );
    }

    public function obtenerSaldo($id_ejidatario)
    {
        try {
            $utilidad = DB::table('utilidad')
            ->where('Id_Utilidad', 1)
                ->first();

            if (!$utilidad) {
                return response()->json(['saldo_disponible' => 0]);
            }

            $monto_base = (float)$utilidad->Monto;

            $deuda_actual = Prestamo::where('Id_Ejidatario', $id_ejidatario)
                ->where('Id_Utilidad', 1)
                ->sum('Cantidad');

            $disponible = $monto_base - $deuda_actual;

            return response()->json([
                'saldo_disponible' => max($disponible, 0)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function actualizarPrestamo(Request $request, $id)
    {
        $prestamo = Prestamo::findOrFail($id);

        $request->validate([
            'motivo' => 'required|string',
            'cantidad' => 'required|numeric|min:0.01',
        ]);

        $prestamo->update([
            'Motivo'   => $request->motivo,
            'Cantidad' => $request->cantidad,
        ]);

        return redirect()->back()->with('success', 'Préstamo actualizado correctamente.');
    }

    public function agregarAbono(Request $request, $id)
    {
        $request->validate(['monto_abono' => 'required|numeric|min:0.01']);

        Abono::create([
            'Id_Prestamo' => $id,
            'Monto'       => $request->monto_abono,
            'Fecha'       => now()
        ]);

        return redirect()->back()->with('success', 'Abono registrado en el historial correctamente');
    }

    public function eliminarPrestamo($id)
    {
        Prestamo::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Préstamo eliminado correctamente.');
    }

    public function obtenerFechaLimite()
    {
        $utilidad = Utilidad::find(1);
        return response()->json(['fecha_limite' => $utilidad?->Fecha_Eliminado ?? '']);
    }

    public function fijarFechaLimite(Request $request)
    {
        $request->validate(['fecha_limite' => 'required|date']);
        $utilidad = Utilidad::find(1);
        if ($utilidad) {
            $utilidad->Fecha_Eliminado = $request->fecha_limite;
            $utilidad->save();
            return response()->json(['success' => true, 'message' => 'Fecha límite actualizada']);
        }
        return response()->json(['success' => false, 'message' => 'No se encontró el registro'], 404);
    }


    public function generarTicketPDF($id) {
        $prestamo = Prestamo::with(['ejidatario.usuario', 'abonos'])->findOrFail($id);

        $reparto = Utilidad::find(1);
        $montoReparto1 = $reparto?->Monto ?? 0;

        $totalAbonado = $prestamo->abonos->sum('Monto');
        $saldoRestante = max($prestamo->Cantidad - $totalAbonado, 0);

        $pdf = \PDF::loadView('cpanel.Repartos.primer-reparto-pdf', [
            'prestamo'      => $prestamo,
            'montoReparto1' => $montoReparto1,
            'totalAbonado'  => $totalAbonado,
            'saldoRestante' => $saldoRestante
        ]);

        return $pdf->stream('ticket-prestamo-'.$id.'.pdf');
    }
}