<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\Utilidad;
use App\Models\Ejidatario;
use App\Models\Usuario;
use App\Models\Descuento;
use App\Models\CatalogoMulta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class Reparto2Controller extends Controller
{
    private $idUtilidad = 2;
    private $idUtilidadReparto1 = 1;

    private $tiposAsamblea = [
        '1ER. ASAMBLEA ELECCION 30/10/24',
        'ASAMBLEA EXTRAORDINARIA 20/11/24',
        'ASAMBLEA 18 DICIEMBRE',
        'ASAMBLEA ENERO',
        'ASAMBLEA MARZO',
        'ASAMBLEA JUNIO',
        'ASAMBLEA SEPTIEMBRE CORTE DE CAJA'
    ];

    private $tiposFaena = [
        'Descuento faenas de saneamient',
        'Descuento faenas de aprovecham'
    ];

    public function index(Request $request)
    {
        $asambleas = $this->tiposAsamblea;
        $query = Ejidatario::with(['usuario', 'descuentos']);

        $hayBusqueda = $request->filled('query');
        $soloDeudores = $request->get('filtrar_descuentos') === 'on';

        // Lógica para que aparezca "en blanco" si no hay búsqueda ni filtro activo
        if (!$hayBusqueda && !$soloDeudores) {
            $ejidatarios = Ejidatario::where('id_ejidatario', 0)->paginate(10);
        } else {
            if ($hayBusqueda) {
                $search = $request->get('query');
                $query->whereHas('usuario', function($q) use ($search) {
                    $q->where('Nombres', 'LIKE', "%$search%")
                        ->orWhere('Apellido_Paterno', 'LIKE', "%$search%")
                        ->orWhere('Apellido_Materno', 'LIKE', "%$search%");
                });
            }

            if ($soloDeudores) {
                $query->whereHas('descuentos', function($q) use ($asambleas) {
                    $q->whereIn('tipo', $asambleas)->where('descuento', '>', 0);
                });
            }

            $ejidatarios = $query->paginate(10)->appends($request->all());
        }

        $catalogoMultas = CatalogoMulta::all();

        return view('cpanel.Descuentos.index', compact('ejidatarios', 'asambleas', 'catalogoMultas'));
    }

    public function indexFaenas(Request $request)
    {
        $faenas = $this->tiposFaena;
        $query = Ejidatario::with(['usuario', 'descuentos']);

        $hayBusqueda = $request->filled('query');
        $soloDeudores = $request->get('filtrar_deudores') === 'on';

        if (!$hayBusqueda && !$soloDeudores) {
            $ejidatarios = Ejidatario::where('id_ejidatario', 0)->paginate(10);
        } else {
            if ($hayBusqueda) {
                $search = $request->get('query');
                $query->whereHas('usuario', function($q) use ($search) {
                    $q->where('Nombres', 'LIKE', "%$search%")
                        ->orWhere('Apellido_Paterno', 'LIKE', "%$search%")
                        ->orWhere('Apellido_Materno', 'LIKE', "%$search%");
                });
            }

            if ($soloDeudores) {
                $query->whereHas('descuentos', function($q) use ($faenas) {
                    $q->whereIn('tipo', $faenas)->where('descuento', '>', 0);
                });
            }

            $ejidatarios = $query->paginate(10)->appends($request->all());
        }

        $catalogoFaenas = CatalogoMulta::all();

        return view('cpanel.Descuentos.faenas', compact('ejidatarios', 'faenas', 'catalogoFaenas'));
    }

    private function estaPeriodoCerrado()
    {
        $reparto = Utilidad::find($this->idUtilidad);
        if ($reparto && $reparto->fecha_limite) {
            return Carbon::now()->startOfDay()->gt(Carbon::parse($reparto->fecha_limite)->startOfDay());
        }
        return false;
    }

    private function esReparto1Cerrado()
    {
        $reparto1 = Utilidad::find($this->idUtilidadReparto1);
        return ($reparto1 && $reparto1->fecha_limite && Carbon::now()->startOfDay()->gt(Carbon::parse($reparto1->fecha_limite)->startOfDay()));
    }

    private function obtenerSaldoDisponibleReal($id_ejidatario, $excluir_prestamo_id = null)
    {
        $utilidad = Utilidad::find($this->idUtilidad);
        $montoReparto2 = $utilidad ? $utilidad->monto : 0;

        $ejidatario = Ejidatario::with('descuentos')->find($id_ejidatario);
        if (!$ejidatario) {
            return 0;
        }

        $queryDeudaR2 = Prestamo::where('id_ejidatario', $id_ejidatario)
            ->where('id_utilidad', $this->idUtilidad);

        if ($excluir_prestamo_id) {
            $queryDeudaR2->where('id_prestamo', '!=', $excluir_prestamo_id);
        }
        $totalDeudaActiva_R2 = $queryDeudaR2->sum('monto_original');

        $saldo_pendiente_r1 = 0;
        if ($this->esReparto1Cerrado()) {
            $saldo_pendiente_r1 = Prestamo::where('id_ejidatario', $id_ejidatario)
                ->where('id_utilidad', $this->idUtilidadReparto1)
                ->where('cantidad', '>', 0)
                ->sum('cantidad');
        }

        $totalDeudaActiva = $totalDeudaActiva_R2 + $saldo_pendiente_r1;
        $totalAsambleas = $ejidatario->descuentos->whereIn('tipo', $this->tiposAsamblea)->sum('descuento');
        $totalFaenas = $ejidatario->descuentos->whereIn('tipo', $this->tiposFaena)->sum('descuento');

        $saldoDisponible = $montoReparto2 - $totalDeudaActiva - $totalAsambleas - $totalFaenas;

        return max(0, $saldoDisponible);
    }

    public function mostrarSegundoReparto(Request $request)
    {
        try {
            $reparto2 = Utilidad::find($this->idUtilidad);
            $montoReparto2 = $reparto2 ? $reparto2->monto : 0;
            $deadlinePasada = $this->estaPeriodoCerrado();
            $esReparto1Cerrado = $this->esReparto1Cerrado();

            $query = Ejidatario::with([
                'usuario',
                'descuentos',
                'prestamos' => function ($q) {
                    $q->where('id_utilidad', $this->idUtilidad);
                }
            ]);

            if ($request->has('query') && $request->query('query') != '') {
                $searchQuery = $request->query('query');
                $query->whereHas('usuario', function ($q) use ($searchQuery) {
                    $q->where('Nombres', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('Apellido_Paterno', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('Apellido_Materno', 'LIKE', '%' . $searchQuery . '%');
                });
            }

            $ejidatarios = $query->paginate(10)->appends($request->except('page'));

            $ejidatarios->getCollection()->transform(function ($ejidatario) use ($montoReparto2, $esReparto1Cerrado) {
                $ejidatario->total_descuento_asambleas = $ejidatario->descuentos
                    ->whereIn('tipo', $this->tiposAsamblea)
                    ->sum('descuento');

                $ejidatario->total_descuento_faenas = $ejidatario->descuentos
                    ->whereIn('tipo', $this->tiposFaena)
                    ->sum('descuento');

                $total_prestamos_r2 = $ejidatario->prestamos->sum('monto_original');

                $saldo_pendiente_r1 = 0;
                if ($esReparto1Cerrado) {
                    $saldo_pendiente_r1 = Prestamo::where('id_ejidatario', $ejidatario->id_ejidatario)
                        ->where('id_utilidad', $this->idUtilidadReparto1)
                        ->where('cantidad', '>', 0)
                        ->sum('cantidad');
                }

                $ejidatario->total_prestamos_reparto2 = $total_prestamos_r2 + $saldo_pendiente_r1;

                $total_calculado = $montoReparto2
                    - $ejidatario->total_descuento_asambleas
                    - $ejidatario->total_descuento_faenas
                    - $ejidatario->total_prestamos_reparto2;

                $ejidatario->total_a_pagar = max(0, $total_calculado);

                return $ejidatario;
            });

            return view('cpanel.Repartos.segundo-reparto', [
                'ejidatarios' => $ejidatarios,
                'montoReparto2' => $montoReparto2,
                'reparto2' => $reparto2,
                'deadlinePasada' => $deadlinePasada
            ]);
        } catch (\Exception $e) {
            Log::error('Error en mostrarSegundoReparto: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el reparto: ' . $e->getMessage());
        }
    }

    public function buscarEjidatarios(Request $request)
    {
        try {
            $query = $request->get('query') ?? $request->get('q');

            if (empty($query) || strlen($query) < 2) {
                return response()->json([]);
            }

            $ejidatarios = Ejidatario::with('usuario')
                ->whereHas('usuario', function($q) use ($query) {
                    $q->where('Nombres', 'LIKE', '%' . $query . '%')
                        ->orWhere('Apellido_Paterno', 'LIKE', '%' . $query . '%')
                        ->orWhere('Apellido_Materno', 'LIKE', '%' . $query . '%');
                })
                ->limit(15)
                ->get();

            $resultados = $ejidatarios->map(function ($e) {
                $saldo = $this->obtenerSaldoDisponibleReal($e->id_ejidatario);

                return [
                    'id' => $e->id_ejidatario,
                    'text' => trim($e->usuario->Nombres . ' ' . $e->usuario->Apellido_Paterno . ' ' . ($e->usuario->Apellido_Materno ?? '')),
                    'saldo_disponible' => $saldo
                ];
            });

            return response()->json($resultados);

        } catch (\Exception $e) {
            \Log::error('Error en buscarEjidatarios: ' . $e->getMessage());
            return response()->json(['error' => 'Error al buscar'], 500);
        }
    }

    public function obtenerSaldoEjidatario($id_ejidatario)
    {
        try {
            $saldoDisponible = $this->obtenerSaldoDisponibleReal($id_ejidatario);
            return response()->json([
                'success' => true,
                'saldo_disponible' => $saldoDisponible
            ]);
        } catch (\Exception $e) {
            Log::error('Error en obtenerSaldoEjidatario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener saldo'
            ], 500);
        }
    }

    public function agregarPrestamo(Request $request)
    {
        try {
            Log::info('Intentando agregar préstamo', $request->all());

            if ($this->estaPeriodoCerrado()) {
                return Redirect::route('reparto.segundo')
                    ->with('error', '¡Periodo cerrado! La fecha límite para agregar préstamos ya pasó.');
            }

            $request->validate([
                'id_ejidatario' => 'required|integer|exists:ejidatario,id_ejidatario',
                'cantidad' => 'required|numeric|min:0.01',
                'motivo' => 'required|string|max:255',
            ]);

            $id_ejidatario = $request->id_ejidatario;
            $cantidad_prestamo = $request->cantidad;

            $saldoDisponible = $this->obtenerSaldoDisponibleReal($id_ejidatario);

            if ($cantidad_prestamo > $saldoDisponible) {
                return Redirect::route('reparto.segundo')
                    ->with('error', '¡Saldo insuficiente! El saldo disponible real es de $' . number_format($saldoDisponible, 2));
            }

            Prestamo::create([
                'id_ejidatario' => $request->id_ejidatario,
                'cantidad' => $request->cantidad,
                'monto_original' => $request->cantidad,
                'total_abonado' => 0,
                'motivo' => $request->motivo,
                'fecha' => now(),
                'estado_prestamo' => 'Debe',
                'id_utilidad' => $this->idUtilidad,
                'id_creo' => Auth::id(),
                'fecha_creo' => now(),
            ]);

            Log::info('Préstamo agregado exitosamente');
            return redirect()->route('reparto.segundo')->with('success', 'Préstamo agregado correctamente al Reparto 2');

        } catch (\Exception $e) {
            Log::error('Error en agregarPrestamo: ' . $e->getMessage());
            return Redirect::route('reparto.segundo')
                ->with('error', 'Error al agregar préstamo: ' . $e->getMessage());
        }
    }

    public function actualizarPrestamo(Request $request, $id)
    {
        try {
            if ($this->estaPeriodoCerrado()) {
                return Redirect::back()
                    ->with('error', '¡Periodo cerrado! La fecha límite para editar ya pasó.');
            }

            $request->validate([
                'motivo' => 'required|string|max:250',
                'cantidad' => 'required|numeric|min:0.01',
            ]);

            $prestamo = Prestamo::findOrFail($id);

            if ($prestamo->id_utilidad == $this->idUtilidadReparto1) {
                return Redirect::back()
                    ->with('error', 'Error: No se puede editar un préstamo del Primer Reparto desde esta pantalla.');
            }

            $nuevaCantidad = $request->cantidad;
            $id_ejidatario = $prestamo->id_ejidatario;
            $saldoDisponible = $this->obtenerSaldoDisponibleReal($id_ejidatario, $id);

            if ($nuevaCantidad > $saldoDisponible) {
                return Redirect::back()
                    ->with('error', '¡Saldo insuficiente! El saldo máximo disponible es de $' . number_format($saldoDisponible, 2));
            }

            if ($nuevaCantidad < $prestamo->total_abonado) {
                return Redirect::back()
                    ->with('error', 'Error: El nuevo monto no puede ser menor que el total ya abonado ($' . $prestamo->total_abonado . ')');
            }

            $prestamo->monto_original = $nuevaCantidad;
            $prestamo->cantidad = $nuevaCantidad - $prestamo->total_abonado;
            $prestamo->motivo = $request->motivo;
            $prestamo->estado_prestamo = ($prestamo->cantidad <= 0) ? 'Pagado' : 'Debe';
            $prestamo->save();

            return Redirect::route('reparto.segundo')
                ->with('success', '¡Préstamo actualizado exitosamente!');

        } catch (\Exception $e) {
            Log::error('Error en actualizarPrestamo: ' . $e->getMessage());
            return Redirect::back()
                ->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function eliminarPrestamo($id)
    {
        try {
            if ($this->estaPeriodoCerrado()) {
                return Redirect::route('reparto.segundo')
                    ->with('error', '¡Periodo cerrado! La fecha límite para eliminar ya pasó.');
            }

            $prestamo = Prestamo::findOrFail($id);

            if ($prestamo->id_utilidad == $this->idUtilidadReparto1) {
                return Redirect::route('reparto.segundo')
                    ->with('error', 'No se puede eliminar un préstamo del Reparto 1.');
            }

            if ($prestamo->total_abonado > 0) {
                return Redirect::route('reparto.segundo')
                    ->with('error', 'No se puede eliminar un préstamo que ya tiene abonos registrados.');
            }

            $prestamo->delete();

            return Redirect::route('reparto.segundo')
                ->with('success', '¡Préstamo eliminado exitosamente!');

        } catch (\Exception $e) {
            Log::error('Error en eliminarPrestamo: ' . $e->getMessage());
            return Redirect::route('reparto.segundo')
                ->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function agregarAbono(Request $request, $id)
    {
        try {
            if ($this->estaPeriodoCerrado()) {
                return Redirect::route('reparto.segundo')
                    ->with('error', '¡Periodo cerrado! La fecha límite para abonar ya pasó.');
            }

            $request->validate([
                'monto_abono' => 'required|numeric|min:0.01',
            ]);

            $prestamo = Prestamo::findOrFail($id);
            $montoAbono = $request->monto_abono;

            if ($montoAbono > $prestamo->cantidad) {
                return Redirect::route('reparto.segundo')
                    ->with('error', 'El abono no puede ser mayor al saldo pendiente ($' . number_format($prestamo->cantidad, 2) . ')');
            }

            $prestamo->total_abonado += $montoAbono;
            $prestamo->cantidad -= $montoAbono;

            if ($prestamo->cantidad <= 0) {
                $prestamo->estado_prestamo = 'Pagado';
            }

            $prestamo->save();

            return Redirect::route('reparto.segundo')
                ->with('success', 'Abono de $' . number_format($montoAbono, 2) . ' registrado correctamente');

        } catch (\Exception $e) {
            Log::error('Error en agregarAbono: ' . $e->getMessage());
            return Redirect::route('reparto.segundo')
                ->with('error', 'Error al registrar abono: ' . $e->getMessage());
        }
    }

    public function generarPDF()
    {
        try {
            $reparto2 = Utilidad::find($this->idUtilidad);
            $montoReparto2 = $reparto2 ? $reparto2->monto : 0;
            $esReparto1Cerrado = $this->esReparto1Cerrado();

            $ejidatarios = Ejidatario::with(['usuario', 'descuentos', 'prestamos' => function($q) {
                $q->where('id_utilidad', $this->idUtilidad);
            }])->get();

            $datos = [];
            foreach ($ejidatarios as $ejidatario) {
                $totalAsambleas = $ejidatario->descuentos->whereIn('tipo', $this->tiposAsamblea)->sum('descuento');
                $totalFaenas = $ejidatario->descuentos->whereIn('tipo', $this->tiposFaena)->sum('descuento');
                $totalPrestamosR2 = $ejidatario->prestamos->sum('monto_original');

                $saldoPendienteR1 = 0;
                if ($esReparto1Cerrado) {
                    $saldoPendienteR1 = Prestamo::where('id_ejidatario', $ejidatario->id_ejidatario)
                        ->where('id_utilidad', $this->idUtilidadReparto1)
                        ->where('cantidad', '>', 0)
                        ->sum('cantidad');
                }

                $totalPrestamos = $totalPrestamosR2 + $saldoPendienteR1;
                $totalAPagar = max(0, $montoReparto2 - $totalAsambleas - $totalFaenas - $totalPrestamos);

                $datos[] = [
                    'nombre' => trim($ejidatario->usuario->Nombres . ' ' . $ejidatario->usuario->Apellido_Paterno . ' ' . $ejidatario->usuario->Apellido_Materno),
                    'asambleas' => $totalAsambleas,
                    'faenas' => $totalFaenas,
                    'prestamos' => $totalPrestamos,
                    'total_pagar' => $totalAPagar
                ];
            }

            $pdf = Pdf::loadView('repartos.pdf-segundo-reparto', [
                'datos' => $datos,
                'montoReparto2' => $montoReparto2,
                'fecha' => now()->format('d/m/Y H:i')
            ]);

            return $pdf->download('segundo-reparto-' . now()->format('Y-m-d') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Error en generarPDF: ' . $e->getMessage());
            return back()->with('error', 'Error al generar PDF: ' . $e->getMessage());
        }
    }

    public function fijarFechaLimite(Request $request)
    {
        try {
            $request->validate(['fecha_limite' => 'required|date']);

            $reparto = Utilidad::find($this->idUtilidad);
            if ($reparto) {
                $reparto->fecha_limite = $request->fecha_limite;
                $reparto->save();
                return response()->json(['success' => true, 'message' => 'Fecha límite guardada correctamente']);
            }
            return response()->json(['success' => false, 'message' => 'No se encontró el registro'], 404);
        } catch (\Exception $e) {
            Log::error('Error en fijarFechaLimite: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function obtenerFechaLimite()
    {
        try {
            $reparto = Utilidad::find($this->idUtilidad);
            return response()->json([
                'success' => true,
                'fecha_limite' => $reparto ? $reparto->fecha_limite : null
            ]);
        } catch (\Exception $e) {
            Log::error('Error en obtenerFechaLimite: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }
}