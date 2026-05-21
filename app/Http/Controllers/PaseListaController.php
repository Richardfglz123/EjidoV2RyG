<?php

namespace App\Http\Controllers;

use App\Models\Sesion;
use App\Models\Evento;
use App\Models\Ejidatario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\AsistenciaExport;
use Maatwebsite\Excel\Facades\Excel;

class PaseListaController extends Controller
{
    public function index()
    {
        $eventos = Evento::leftJoin('Categoria_Evento as c', 'Evento.Id_Categoria_Evento', '=', 'c.Id_Categoria_Evento')
            ->select('Evento.*', 'c.Nombre_Categoria')
            ->get();

        $totalEjidatarios = Ejidatario::count();

        $sesiones = Sesion::with(['evento.categoria'])
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

        $sesion = Sesion::firstOrCreate([
            'Tipo'          => $request->tipo,
            'Id_Referencia' => $request->id_referencia,
            'Fecha'         => $request->fecha
        ]);
        $presentes = DB::table('PaseLista as a')
            ->join('Ejidatario as e', 'a.Id_Ejidatario', '=', 'e.Id_Ejidatario')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->where('a.Id_Sesion', $sesion->Id_Sesion)
            ->select(
                'e.Num_Ejidatario',
                'u.Nombres',
                'u.Apellido_Paterno',
                'u.Apellido_Materno',
                'a.Fecha as FechaAsistencia'
            )
            ->orderBy('a.Fecha', 'desc')
            ->get()
            ->map(function ($p) {
                $p->Hora = \Carbon\Carbon::parse($p->FechaAsistencia)->format('H:i:s');
                return $p;
            });

        $evento = Evento::find($request->id_referencia);

        return view('cpanel.PaseLista.escanear', compact('sesion', 'evento', 'presentes'));
    }

    public function marcarAsistencia(Request $request)
    {
        // Log para depuración: Ver qué recibe exactamente el servidor
        \Log::info('PaseLista - Datos recibidos:', $request->all());

        try {
            $id_recibido = $request->input('id_sesion');
            $qr_data = $request->input('qr_data');

            // 1. Validación de existencia de datos
            if (empty($id_recibido) || empty($qr_data)) {
                return response()->json(['success' => false, 'message' => "Datos de sesión o QR ausentes"]);
            }

            // 2. Validación de la Sesión (Forzamos a que sea un ID existente)
            $sesion = Sesion::find((int)$id_recibido);

            if (!$sesion) {
                \Log::error("PaseLista - Sesión no encontrada en BD con ID: " . $id_recibido);
                return response()->json(['success' => false, 'message' => "La sesión actual no existe en el sistema."]);
            }

            // 3. Limpieza y búsqueda de Ejidatario
            $cadenaQR = strtoupper(trim($qr_data));

            $mejorMatch = DB::table('Ejidatario as e')
                ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
                ->select('e.Id_Ejidatario', 'e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno')
                ->get()
                ->sortBy(function($c) use ($cadenaQR) {
                    $nombreCompleto = strtoupper($c->Nombres . ' ' . $c->Apellido_Paterno . ' ' . $c->Apellido_Materno);
                    return levenshtein($cadenaQR, $nombreCompleto);
                })
                ->first();

            // Umbral de seguridad (levenshtein < 20 es una coincidencia muy buena)
            if (!$mejorMatch || levenshtein($cadenaQR, strtoupper($mejorMatch->Nombres . ' ' . $mejorMatch->Apellido_Paterno . ' ' . $mejorMatch->Apellido_Materno)) > 20) {
                return response()->json(['success' => false, 'message' => "Ejidatario no encontrado"]);
            }

            // 4. Inserción o Actualización (El 'updateOrInsert' previene duplicados y asegura el registro)
            $registrado = DB::table('PaseLista')->updateOrInsert(
                [
                    'Id_Sesion'     => (int)$sesion->Id_Sesion,
                    'Id_Ejidatario' => (int)$mejorMatch->Id_Ejidatario
                ],
                [
                    'Asistencia' => 1,
                    'Fecha'      => now()->format('Y-m-d H:i:s')
                ]
            );

            return response()->json([
                'success'  => true,
                'nombre'   => $mejorMatch->Nombres . ' ' . $mejorMatch->Apellido_Paterno . ' ' . $mejorMatch->Apellido_Materno
            ]);

        } catch (\Exception $e) {
            \Log::error("PaseLista - Error crítico: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => "Error de sistema: " . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        DB::table('PaseLista')->where('Id_Sesion', $id)->delete();
        Sesion::destroy($id);
        return redirect()->back()->with('success', 'Sesión eliminada.');
    }

    public function exportarPdf($id)
    {
        $sesion = Sesion::with('evento')->findOrFail($id);
        $idsAsistentes = DB::table('PaseLista')->where('Id_Sesion', $id)->pluck('Id_Ejidatario');

        $asistieron = DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->whereIn('e.Id_Ejidatario', $idsAsistentes)
            ->select('e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno')->get();

        $noAsistieron = DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->whereNotIn('e.Id_Ejidatario', $idsAsistentes)
            ->select('e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno')->get();

        $total = Ejidatario::count();

        $pdf = Pdf::loadView('cpanel.PaseLista.asistenciapdf', compact('sesion', 'asistieron', 'noAsistieron', 'total'));
        return $pdf->stream('Reporte_Asistencia_'.$id.'.pdf');
    }

    public function exportarExcel($id)
    {
        return Excel::download(new AsistenciaExport($id), 'Reporte_Asistencia_'.$id.'.xlsx');
    }
}