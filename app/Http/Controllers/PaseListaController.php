<?php

namespace App\Http\Controllers;

use App\Models\Sesion;
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

        // Sesiones con conteo de asistencias corregido
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

        // JOIN corregido para mostrar nombres de quienes ya estaban registrados en la sesión
        $presentes = DB::table('PaseLista as a')
            ->join('Ejidatario as e', 'a.Id_Ejidatario', '=', 'e.Id_Ejidatario')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->where('a.Id_Sesion', $sesion->Id_Sesion)
            ->select(
                'e.Num_Ejidatario',
                'u.Nombres',
                'u.Apellido_Paterno',
                'u.Apellido_Materno',
                'a.Fecha as Hora'
            )
            ->orderBy('a.Fecha', 'desc')
            ->get();

        $evento = Evento::find($request->id_referencia);

        return view('cpanel.PaseLista.escanear', compact('sesion', 'evento', 'presentes'));
    }

    public function marcarAsistencia(Request $request)
    {
        try {
            $id_sesion = $request->id_sesion;
            $sesion = Sesion::findOrFail($id_sesion);

            // Limpieza del QR
            $raw = strtoupper($request->qr_data);
            $soloLetrasQR = preg_replace('/[^A-Z0-9ÁÉÍÓÚÑ\s]/', '', $raw);

            if (empty($soloLetrasQR)) {
                return response()->json(['success' => false, 'message' => "QR ilegible o vacío"]);
            }

            // Búsqueda del Ejidatario usando JOIN para evitar fallos de relación Eloquent
            $ejidatario = DB::table('Ejidatario as e')
                ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
                ->where(DB::raw("UPPER(REPLACE(REPLACE(REPLACE(CONCAT(u.Nombres, u.Apellido_Paterno, u.Apellido_Materno), ' ', ''), '.', ''), ',', ''))"),
                    'LIKE',
                    "%$soloLetrasQR%"
                )
                ->select('e.Id_Ejidatario', 'e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno')
                ->first();

            if (!$ejidatario) {
                return response()->json(['success' => false, 'message' => "No registrado: " . substr($raw, 0, 15)]);
            }

            // Registro de asistencia (PaseLista suele ser el nombre de la tabla en tu DB)
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
                'nombre' => $ejidatario->Nombres . ' ' . $ejidatario->Apellido_Paterno
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Error: " . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            // Eliminamos asistencias vinculadas primero por integridad
            DB::table('PaseLista')->where('Id_Sesion', $id)->delete();
            Sesion::destroy($id);
            DB::commit();
            return redirect()->back()->with('success', 'Sesión y asistencias eliminadas.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al eliminar la sesión.');
        }
    }

    public function exportarPdf($id)
    {
        $sesion = Sesion::with('evento')->findOrFail($id);

        // Obtener asistentes con JOIN para el PDF
        $asistieron = DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->join('PaseLista as p', 'e.Id_Ejidatario', '=', 'p.Id_Ejidatario')
            ->where('p.Id_Sesion', $id)
            ->select('e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno')
            ->get();

        // Obtener NO asistentes (aquellos que no están en PaseLista para esta sesión)
        $idsAsistentes = DB::table('PaseLista')->where('Id_Sesion', $id)->pluck('Id_Ejidatario');

        $noAsistieron = DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->whereNotIn('e.Id_Ejidatario', $idsAsistentes)
            ->select('e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno')
            ->get();

        $total = Ejidatario::count();

        $pdf = Pdf::loadView('cpanel.PaseLista.asistenciapdf', compact('sesion', 'asistieron', 'noAsistieron', 'total'));
        return $pdf->stream('Reporte_Asistencia_'.$id.'.pdf');
    }
}