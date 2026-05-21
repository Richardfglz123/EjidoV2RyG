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
use Carbon\Carbon;

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
                'a.Fecha'
            )
            ->orderBy('a.Fecha', 'desc')
            ->get()
            ->map(function ($p) {
                // Si la fecha es igual a la fecha de hoy pero la hora es 00:00:00,
                // mostramos al menos la hora actual o un mensaje.
                // PERO: Si la fecha guardada NO tiene hora, Carbon devolverá 00:00:00.
                $fecha = \Carbon\Carbon::parse($p->Fecha);

                // FORZADO: Si la hora es exactamente 00:00:00, intentamos ver si el campo
                // tiene información oculta o simplemente mostramos que no hay hora.
                $p->Hora = ($fecha->format('H:i:s') === '00:00:00') ? "Sin hora" : $fecha->format('H:i:s');

                return $p;
            });

        $evento = Evento::find($request->id_referencia);

        return view('cpanel.PaseLista.escanear', compact('sesion', 'evento', 'presentes'));
    }

    public function marcarAsistencia(Request $request)
    {
        try {
            $id_recibido = $request->input('id_sesion');
            $qr_data = $request->input('qr_data');

            if (!$id_recibido || !$qr_data) {
                return response()->json(['success' => false, 'message' => "Faltan datos de sesión o QR"]);
            }

            $sesion = Sesion::find($id_recibido);

            if (!$sesion) {
                $sesion = Sesion::where('Id_Referencia', $id_recibido)
                    ->orderBy('Fecha', 'desc')
                    ->first();
            }

            if (!$sesion) {
                return response()->json(['success' => false, 'message' => "Sesión no encontrada en el sistema."]);
            }

            $raw = strtoupper(str_replace(['Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', 'Z', 'S', 'C'], ['A', 'E', 'I', 'O', 'U', 'N', 'S', 'S', 'S'], $qr_data));
            $raw = preg_replace(['/\([^)]+\)/', '/[0-9.,-]/'], ['', ' '], $raw);
            $palabras = array_filter(explode(' ', $raw), fn($p) => strlen(trim($p)) > 1 && trim($p) !== 'HERM');

            if (empty($palabras)) return response()->json(['success' => false, 'message' => "QR ilegible"]);

            $cadenaQR = implode(' ', $palabras);
            $mejorMatch = DB::table('Ejidatario as e')
                ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
                ->select('e.Id_Ejidatario', 'e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno',
                    DB::raw("REPLACE(REPLACE(REPLACE(UPPER(CONCAT_WS(' ', u.Nombres, u.Apellido_Paterno, u.Apellido_Materno)), 'Z', 'S'), 'C', 'S'), 'Ç', 'S') as nombre_normalizado"))
                ->get()
                ->sortBy(fn($c) => levenshtein($cadenaQR, $c->nombre_normalizado))
                ->first();

            if (!$mejorMatch || levenshtein($cadenaQR, $mejorMatch->nombre_normalizado) > 12)
                return response()->json(['success' => false, 'message' => "Ejidatario no encontrado"]);

            DB::table('PaseLista')->updateOrInsert(
                ['Id_Sesion' => (int)$sesion->Id_Sesion, 'Id_Ejidatario' => $mejorMatch->Id_Ejidatario],
                ['Asistencia' => 1, 'Fecha' => now()]
            );

            return response()->json([
                'success'  => true,
                'num_ejid' => (int)$mejorMatch->Num_Ejidatario,
                'nombre'   => $mejorMatch->Nombres . ' ' . $mejorMatch->Apellido_Paterno
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Error: " . $e->getMessage()]);
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