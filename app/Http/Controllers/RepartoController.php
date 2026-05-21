<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestamo;
use App\Models\Utilidad;
use App\Models\Usuario;
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
        ]);

        $utilidad = Utilidad::findOrFail($id);

        // Intentamos obtener al usuario por varias vías:
        // 1. Auth::user()
        // 2. Si usas un nombre de sesión distinto, prueba con el helper session() si tienes el ID guardado
        $user = Auth::user();

        if ($user) {
            $utilidad->Id_Modificado = $user->Nombres . ' ' . $user->Apellido_Paterno . ' ' . $user->Apellido_Materno;
        } else {
            // DEPURACIÓN: Si esto sigue entrando aquí, es que el 'auth' no está protegiendo la ruta
            // Puedes intentar obtenerlo por el ID si lo tienes en sesión
            $utilidad->Id_Modificado = 'Administrador';
        }

        $utilidad->Monto = $request->monto;
        $utilidad->Año = date('Y');
        $utilidad->Fecha_Modificado = now();
        $utilidad->save();

        return redirect()->back()->with('success', 'Cambios guardados exitosamente.');
    }

    public function index(Request $request)
    {
        $utilidades = Utilidad::all();
        $usuarios = Usuario::all(); // CORREGIDO: Uso de clase con mayúscula
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

        // 1. Traemos los préstamos haciendo Join para asegurar los datos en entornos estrictos (Linux)
        $prestamos = Prestamo::select(
            'Prestamo.*',
            'usuario.Nombres as usuario_nombres',
            'usuario.Apellido_Paterno as usuario_paterno',
            'usuario.Apellido_Materno as usuario_materno'
        )
            ->leftJoin('Ejidatario', 'Prestamo.Id_Ejidatario', '=', 'Ejidatario.Id_Ejidatario')
            ->leftJoin('usuario', 'Ejidatario.Id_usuario', '=', 'usuario.Id_usuario')
            ->where('Prestamo.Id_Utilidad', $idPrimerReparto)
            ->withSum('abonos as total_abonado', 'Monto')
            ->paginate(10);

        // 2. Mapeamos los datos planos para inyectarlos en la estructura que Blade espera leer
        $prestamos->getCollection()->transform(function ($prestamo) {
            if (!$prestamo->ejidatario) {
                $prestamo->setRelation('ejidatario', new \App\Models\Ejidatario());
            }
            if (!$prestamo->ejidatario->usuario) {
                $prestamo->ejidatario->setRelation('usuario', new \App\Models\Usuario());
            }

            // Seteamos los valores recuperados del join directo
            $prestamo->ejidatario->usuario->Nombres = $prestamo->usuario_nombres ?? 'Ejidatario';
            $prestamo->ejidatario->usuario->Apellido_Paterno = $prestamo->usuario_paterno ?? '';
            $prestamo->ejidatario->usuario->Apellido_Materno = $prestamo->usuario_materno ?? '';

            return $prestamo;
        });

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
            ->with(['ejidatario.usuario'])
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

        // Buscamos directamente haciendo join con Query Builder para evitar fallos de relación
        $ejidatarios = DB::table('Ejidatario')
            ->join('usuario', 'Ejidatario.Id_usuario', '=', 'usuario.Id_usuario')
            ->where('usuario.Nombres', 'LIKE', "%{$q}%")
            ->orWhere('usuario.Apellido_Paterno', 'LIKE', "%{$q}%")
            ->orWhere('usuario.Apellido_Materno', 'LIKE', "%{$q}%")
            ->select('Ejidatario.Id_Ejidatario', 'usuario.Nombres', 'usuario.Apellido_Paterno', 'usuario.Apellido_Materno')
            ->get();

        return response()->json(
            $ejidatarios->map(function ($e) {
                return [
                    'id' => $e->Id_Ejidatario,
                    'text' => $e->Nombres . ' ' . $e->Apellido_Paterno . ' ' . $e->Apellido_Materno
                ];
            })
        );
    }

    public function obtenerSaldo($id_ejidatario)
    {
        try {
            // CORREGIDO: Uso del Query Builder en minúsculas sustituido por consistencia con el Modelo
            $utilidad = Utilidad::find(1);

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
        $prestamo = Prestamo::select(
            'Prestamo.*',
            'usuario.Nombres as usuario_nombres',
            'usuario.Apellido_Paterno as usuario_paterno',
            'usuario.Apellido_Materno as usuario_materno'
        )
            ->leftJoin('Ejidatario', 'Prestamo.Id_Ejidatario', '=', 'Ejidatario.Id_Ejidatario')
            ->leftJoin('usuario', 'Ejidatario.Id_usuario', '=', 'usuario.Id_usuario')
            ->with(['abonos'])
            ->findOrFail($id);

        if (!$prestamo->ejidatario) {
            $prestamo->setRelation('ejidatario', new \App\Models\Ejidatario());
        }
        if (!$prestamo->ejidatario->usuario) {
            $prestamo->ejidatario->setRelation('usuario', new \App\Models\Usuario());
        }

        $prestamo->ejidatario->usuario->Nombres = $prestamo->usuario_nombres ?? 'Ejidatario';
        $prestamo->ejidatario->usuario->Apellido_Paterno = $prestamo->usuario_paterno ?? '';
        $prestamo->ejidatario->usuario->Apellido_Materno = $prestamo->usuario_materno ?? '';

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