<?php

namespace App\Http\Controllers;

use App\Models\Sesion;
use App\Models\PaseLista;
use App\Models\Evento;
use App\Models\Ejidatario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PaseListaController extends Controller
{
    public function index()
    {
        $eventos = Evento::all();
        $totalEjidatarios = Ejidatario::count();
        $sesiones = Sesion::with('evento')
            ->select('Sesion.*')
            ->addSelect([
                'asistencias_count' => DB::table('PaseLista')
                    ->whereColumn('Id_Sesion', 'Sesion.Id_Sesion')
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
            ]
        );

        $presentes = DB::table('PaseLista as a')
            ->join('Ejidatario as e', 'a.Id_Ejidatario', '=', 'e.Id_Ejidatario')
            ->join('Usuario as u', 'e.Id_Usuario', '=', 'u.Id_Usuario')
            ->where('a.Id_Sesion', $sesion->Id_Sesion)
            ->select('e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'a.Fecha as Hora')
            ->orderBy('a.Fecha', 'desc')
            ->get();

        $evento = Evento::find($request->id_referencia);

        return view('cpanel.PaseLista.escanear', compact('sesion', 'evento', 'presentes'));
    }

    public function marcarAsistencia(Request $request) {
        try {
            $id_sesion = $request->id_sesion;
            $sesion = Sesion::findOrFail($id_sesion);

            $raw = strtoupper($request->qr_data);
            $soloLetrasQR = preg_replace('/[^A-Z]/', '', $raw);

            if (empty($soloLetrasQR)) {
                return response()->json(['success' => false, 'message' => "QR ilegible"]);
            }
            $ejidatario = Ejidatario::whereHas('usuario', function($q) use ($soloLetrasQR) {
                $q->where(DB::raw("UPPER(REPLACE(REPLACE(REPLACE(CONCAT(Nombres, Apellido_Paterno, Apellido_Materno), ' ', ''), '.', ''), ',', ''))"),
                    'LIKE',
                    "%$soloLetrasQR%"
                );
            })->first();

            if (!$ejidatario) {
                return response()->json(['success' => false, 'message' => "No registrado: " . substr($raw, 0, 15)]);
            }

            DB::table('PaseLista')->updateOrInsert(
                [
                    'Id_Sesion' => $sesion->Id_Sesion,
                    'Id_Ejidatario' => $ejidatario->Id_Ejidatario
                ],
                [
                    'Asistencia' => 1,
                    'Fecha' => now(),
                    'Id_Actividad' => ($sesion->Tipo === 'Actividad') ? $sesion->Id_Referencia : null
                ]
            );

            return response()->json([
                'success' => true,
                'num_ejid' => $ejidatario->Num_Ejidatario,
                'nombre' => $ejidatario->usuario->Nombres . ' ' . $ejidatario->usuario->Apellido_Paterno
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Error de base de datos: " . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            DB::table('PaseLista')->where('Id_Sesion', $id)->delete();
            Sesion::destroy($id);
            DB::commit();
            return redirect()->back()->with('success', 'Sesión eliminada.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al eliminar.');
        }
    }

    public function exportarPdf($id)
    {
        $sesion = Sesion::with('evento')->findOrFail($id);
        $idsAsistentes = DB::table('PaseLista')->where('Id_Sesion', $id)->pluck('Id_Ejidatario');

        $asistieron = Ejidatario::with('usuario')->whereIn('Id_Ejidatario', $idsAsistentes)->get();
        $noAsistieron = Ejidatario::with('usuario')->whereNotIn('Id_Ejidatario', $idsAsistentes)->get();
        $total = Ejidatario::count();

        $pdf = Pdf::loadView('cpanel.PaseLista.asistenciapdf', compact('sesion', 'asistieron', 'noAsistieron', 'total'));
        return $pdf->stream('Reporte_'.$id.'.pdf');
    }
}