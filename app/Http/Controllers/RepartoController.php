<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Prestamo;
use App\Models\Utilidad;
use App\Models\Ejidatario;
use App\Models\Usuario;
use Carbon\Carbon;

class RepartoController extends Controller
{
    public function menu()
    {
        $data = [
            'finiquito_saneamiento'     => Utilidad::find(1),
            'primer_reparto'            => Utilidad::find(2),
            'segundo_reparto'           => Utilidad::find(3),
            'finiquito_utilidades'      => Utilidad::find(4),
            'descuento_saneamiento'     => Utilidad::find(5),
            'descuento_aprovechamiento' => Utilidad::find(6),
            'descuento_asambleas'       => Utilidad::find(7),
        ];

        return view('cpanel.monto.menu', $data);
    }

    public function index(Request $request)
    {
        $utilidades = Utilidad::all();
        $usuarios = Usuario::all();

        $idSeleccionado = $request->input('id_utilidad');
        $utilidadSeleccionada = $idSeleccionado ? Utilidad::find($idSeleccionado) : null;

        return view('cpanel.monto.monto', compact('utilidades', 'utilidadSeleccionada', 'usuarios'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0',
            'anio' => 'required|numeric',
        ]);

        $utilidad = Utilidad::findOrFail($id);
        $utilidad->UtilidadAnual = $request->monto;
        $utilidad->Año = $request->anio;
        $utilidad->save();

        return redirect()->route('menu')->with('success', 'Reparto actualizado correctamente.');
    }

    public function mostrarPrimerReparto()
    {
        $idPrimerReparto = 1;
        $reparto1 = Utilidad::find($idPrimerReparto);
        $montoReparto1 = $reparto1?->monto ?? 0;
        $fechaLimite = $reparto1?->fecha_limite;
        $deadlinePasada = $fechaLimite
            ? Carbon::now()->startOfDay()->gt(Carbon::parse($fechaLimite)->startOfDay())
            : false;

        $prestamos = Prestamo::where('Id_Utilidad', $idPrimerReparto)
            ->with('ejidatario.usuario')
            ->paginate(10);

        return view('cpanel.repartos.primer-reparto', compact(
            'prestamos', 'montoReparto1', 'deadlinePasada', 'reparto1'
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
            'Motivo' => $request->motivo,
            'Cantidad' => $request->cantidad,
            'Saldo_Continuo' => $request->cantidad,
            'Fecha' => now(),
            'Id_Utilidad' => 1,
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
        $ejidatario = \App\Models\Ejidatario::with('usuario')->find($id_ejidatario);

        if (!$ejidatario) {
            return response()->json(['error' => 'Ejidatario no encontrado'], 404);
        }

        $total_prestamos = \App\Models\Prestamo::where('id_ejidatario', $id_ejidatario)->sum('Cantidad');
        $prestamo_actual = \App\Models\Prestamo::where('id_ejidatario', $id_ejidatario)->sum('Saldo_Continuo');
        $saldo_disponible = max($total_prestamos - $prestamo_actual, 0);

        return response()->json([
            'nombre' => $ejidatario->usuario->Nombres . ' ' . $ejidatario->usuario->Apellido_Paterno,
            'descripcion' => '-', // opcional: último motivo o vacío
            'total_repartos' => $total_prestamos,
            'prestamo_actual' => $prestamo_actual,
            'saldo_disponible' => $saldo_disponible
        ]);
    }


    public function actualizarPrestamo(Request $request, $id)
    {
        $prestamo = \App\Models\Prestamo::findOrFail($id);

        $request->validate([
            'motivo' => 'required|string',
            'cantidad' => 'required|numeric|min:0.01',
        ]);

        $prestamo->update([
            'Motivo' => $request->motivo,
            'Cantidad' => $request->cantidad,
            'Saldo_Continuo' => $prestamo->Saldo_Continuo + ($request->cantidad - $prestamo->Cantidad),
        ]);

        return redirect()->back()->with('success', 'Préstamo actualizado correctamente.');
    }

    public function agregarAbono(Request $request, $id)
    {
        $prestamo = \App\Models\Prestamo::findOrFail($id);

        $request->validate([
            'monto_abono' => 'required|numeric|min:0.01',
        ]);

        $prestamo->Saldo_Continuo = max($prestamo->Saldo_Continuo - $request->monto_abono, 0);
        $prestamo->save();

        return redirect()->back()->with('success', 'Abono registrado correctamente.');
    }

    public function eliminarPrestamo($id)
    {
        Prestamo::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Préstamo eliminado correctamente.');
    }

    public function obtenerFechaLimite()
    {
        $utilidad = Utilidad::find(2);
        return response()->json(['fecha_limite' => $utilidad?->Fecha_Eliminado ?? '']);
    }

    public function fijarFechaLimite(Request $request)
    {
        $request->validate(['fecha_limite' => 'required|date']);
        $utilidad = Utilidad::find(2);
        if ($utilidad) {
            $utilidad->Fecha_Eliminado = $request->fecha_limite;
            $utilidad->save();
            return response()->json(['success' => true, 'message' => 'Fecha límite actualizada']);
        }
        return response()->json(['success' => false, 'message' => 'No se encontró el registro'], 404);
    }
}