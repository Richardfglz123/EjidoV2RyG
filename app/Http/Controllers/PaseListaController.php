<?php

namespace App\Http\Controllers;

use App\Models\Sesion;
use App\Models\PaseLista;
use Illuminate\Http\Request;
use App\Models\Evento;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AsistenciaExport;
use App\Models\Ejidatario;
use Illuminate\Support\Facades\DB;

class PaseListaController extends Controller
{
    public function index()
    {
        $eventos = Evento::all();
        $totalEjidatarios = \App\Models\Ejidatario::count();

        $sesiones = Sesion::with('evento')
            ->select('*')
            ->addSelect([
                'asistencias_count' => DB::table('asistencia_sesion')
                    ->whereColumn('Id_Sesion', 'sesion.Id_Sesion') // Ajusta 'sesion' al nombre real de tu tabla de sesiones
                    ->selectRaw('count(*)')
            ])
            ->orderBy('Fecha', 'desc')
            ->get();

        return view('cpanel.PaseLista.paselista', compact('eventos', 'sesiones', 'totalEjidatarios'));
    }

    public function registrarAsistencia(Request $request)
    {
        $request->validate([
            'id_referencia' => 'required',
            'tipo'          => 'required',
            'fecha'         => 'required|date',
        ]);

        $sesion = Sesion::firstOrCreate(
            [
                'Tipo'          => $request->tipo,
                'Id_Referencia' => $request->id_referencia,
                'Fecha'         => $request->fecha
            ],
            [
                'Id_Creo'       => auth()->user()->username ?? 'lou',
                'Fecha_Creo'    => now(),
            ]
        );
        $presentes = DB::table('asistencia_sesion as a')
            ->join('Ejidatario as e', 'a.Id_Ejidatario', '=', 'e.Id_Ejidatario')
            ->join('Usuario as u', 'e.Id_Usuario', '=', 'u.Id_Usuario')
            ->where('a.Id_Sesion', $sesion->Id_Sesion)
            ->select('e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'a.Fecha_Hora as Hora')
            ->orderBy('a.Fecha_Hora', 'desc')
            ->get();

        $evento = Evento::find($request->id_referencia);

        return view('cpanel.PaseLista.escanear', compact('sesion', 'evento', 'presentes'));
    }

    public function marcarAsistencia(Request $request) {
        try {
            $payload = $request->qr_data;

            $limpio = str_ireplace(['\n', "\\n", "\n", "\r", ".", ","], ' ', $payload);
            $limpio = preg_replace('/\s+/', ' ', trim($limpio));

            $terminos = explode(' ', $limpio);

            $ejidatario = null;

            if (count($terminos) > 0 && is_numeric($terminos[0])) {
                $ejidatario = Ejidatario::where('Num_Ejidatario', $terminos[0])->first();
            }

            if (!$ejidatario) {
                $ejidatario = Ejidatario::whereHas('usuario', function($q) use ($limpio) {
                    // Esta línea es la clave: limpia los saltos de línea y puntos de la DB antes de comparar
                    $q->where(DB::raw("UPPER(REPLACE(REPLACE(REPLACE(CONCAT(Nombres, ' ', Apellido_Paterno, ' ', Apellido_Materno), '\\\\n', ''), '.', ''), ',', ''))"),
                        'LIKE',
                        '%' . strtoupper($limpio) . '%'
                    );
                })->first();
            }

            if (!$ejidatario && count($terminos) >= 2) {
                $ejidatario = Ejidatario::whereHas('usuario', function($q) use ($terminos) {
                    $q->where('Nombres', 'LIKE', '%' . $terminos[0] . '%')
                        ->where('Apellido_Paterno', 'LIKE', '%' . $terminos[1] . '%');
                })->first();
            }

            if (!$ejidatario) {
                return response()->json([
                    'success' => false,
                    'message' => "No se encontró a [$limpio]. Verifique puntos o espacios en el registro."
                ]);
            }

            $yaExiste = DB::table('asistencia_sesion')
                ->where('Id_Sesion', $request->id_sesion)
                ->where('Id_Ejidatario', $ejidatario->Id_Ejidatario)
                ->exists();

            if ($yaExiste) {
                return response()->json([
                    'success' => false,
                    'message' => "Asistencia ya registrada para " . $ejidatario->usuario->Nombres
                ]);
            }

            DB::table('asistencia_sesion')->insert([
                'Id_Sesion' => $request->id_sesion,
                'Id_Ejidatario' => $ejidatario->Id_Ejidatario,
                'Fecha_Hora' => now()
            ]);

            return response()->json([
                'success' => true,
                'nombre' => $ejidatario->usuario->Nombres . ' ' . $ejidatario->usuario->Apellido_Paterno
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function historial()
    {
        $totalEjidatarios = \App\Models\Ejidatario::count();

        $sesiones = Sesion::with('evento')
            ->withCount('asistencias')
            ->orderBy('Fecha', 'desc')
            ->get();

        return view('cpanel.PaseLista.historial', compact('sesiones', 'totalEjidatarios'));
    }

    public function exportarExcel($id)
    {
        $sesion = Sesion::findOrFail($id);
        return Excel::download(new AsistenciaExport($id), 'asistencia_evento_'.$id.'.xlsx');
    }

    public function exportarPdf($id)
    {
        $sesion = Sesion::with('evento')->findOrFail($id);

        $idsAsistentes = DB::table('asistencia_sesion')
            ->where('Id_Sesion', $id)
            ->pluck('Id_Ejidatario')
            ->toArray();

        $asistieron = Ejidatario::with('usuario')->whereIn('Id_Ejidatario', $idsAsistentes)->get();
        $noAsistieron = Ejidatario::with('usuario')->whereNotIn('Id_Ejidatario', $idsAsistentes)->get();

        $total = Ejidatario::count();

        $pdf = Pdf::loadView('cpanel.PaseLista.asistenciapdf', compact('sesion', 'asistieron', 'noAsistieron', 'total'));
        return $pdf->stream('Reporte_Sesion_'.$id.'.pdf');
    }

    public function generarPlanillaQR()
    {
        $ejidatarios = Ejidatario::with('usuario')->get();

        // Esta vista la creamos para imprimir las tarjetas con el QR
        return view('cpanel.PaseLista.planilla_qr', compact('ejidatarios'));
    }
}