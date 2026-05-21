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
            ->orderBy('Sesion.Fecha', 'desc')
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

        // Sesión única por evento y fecha
        $sesion = Sesion::firstOrCreate([
            'Tipo'          => $request->tipo,
            'Id_Referencia' => $request->id_referencia,
            'Fecha'         => $request->fecha
        ]);

        $presentes = DB::table('PaseLista as a')
            ->join('Ejidatario as e', 'a.Id_Ejidatario', '=', 'e.Id_Ejidatario')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->where('a.Id_Sesion', $sesion->Id_Sesion)
            ->select('e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno', 'a.Fecha as Hora')
            ->orderBy('a.Fecha', 'desc')
            ->get();

        $evento = Evento::find($request->id_referencia);
        return view('cpanel.PaseLista.escanear', compact('sesion', 'evento', 'presentes'));
    }

    public function marcarAsistencia(Request $request)
    {
        try {
            // Recibimos el ID exacto enviado desde el JS (que debe ser el Id_Sesion)
            $id_sesion = $request->input('id_sesion');
            $qr_data = $request->input('qr_data');

            if (!$id_sesion || !$qr_data) {
                return response()->json(['success' => false, 'message' => "Datos incompletos"]);
            }

            // Validamos que la sesión exista
            $sesion = Sesion::find($id_sesion);
            if (!$sesion) {
                return response()->json(['success' => false, 'message' => "Sesión no válida"]);
            }

            // LIMPIEZA QR
            $raw = strtoupper($qr_data);
            $raw = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', 'Z', 'S', 'C'], ['A', 'E', 'I', 'O', 'U', 'N', 'S', 'S', 'S'], $raw);
            $raw = preg_replace(['/\([^)]+\)/', '/[0-9.,-]/'], ['', ' '], $raw);
            $palabras = array_filter(explode(' ', $raw), fn($p) => strlen(trim($p)) > 1 && trim($p) !== 'HERM');

            if (empty($palabras)) return response()->json(['success' => false, 'message' => "QR ilegible"]);

            $cadenaQR = implode(' ', $palabras);
            $concatBD = "REPLACE(REPLACE(REPLACE(UPPER(CONCAT_WS(' ', u.Nombres, u.Apellido_Paterno, u.Apellido_Materno)), 'Z', 'S'), 'C', 'S'), 'Ç', 'S')";

            $candidatos = DB::table('Ejidatario as e')
                ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
                ->select('e.Id_Ejidatario', 'e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno', DB::raw("$concatBD as nombre_normalizado"))
                ->get();

            $mejorMatch = null; $distanciaMinima = 999;
            foreach ($candidatos as $c) {
                $distancia = levenshtein($cadenaQR, $c->nombre_normalizado);
                if ($distancia < $distanciaMinima) { $distanciaMinima = $distancia; $mejorMatch = $c; }
            }

            if (!$mejorMatch || $distanciaMinima > 12) return response()->json(['success' => false, 'message' => "Ejidatario no encontrado"]);

            // REGISTRO ÚNICO (Evita duplicados)
            DB::table('PaseLista')->updateOrInsert(
                ['Id_Sesion' => (int)$sesion->Id_Sesion, 'Id_Ejidatario' => $mejorMatch->Id_Ejidatario],
                ['Asistencia' => 1, 'Fecha' => now()]
            );

            return response()->json([
                'success' => true,
                'num_ejid' => (int)$mejorMatch->Num_Ejidatario,
                'nombre' => $mejorMatch->Nombres . ' ' . $mejorMatch->Apellido_Paterno
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        DB::table('PaseLista')->where('Id_Sesion', $id)->delete();
        Sesion::destroy($id);
        return redirect()->back()->with('success', 'Eliminado.');
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

        $pdf = Pdf::loadView('cpanel.PaseLista.asistenciapdf', compact('sesion', 'asistieron', 'noAsistieron', 'total' => Ejidatario::count()));
        return $pdf->stream('Asistencia_'.$id.'.pdf');
    }

    public function exportarExcel($id)
    {
        return Excel::download(new AsistenciaExport($id), 'Asistencia_'.$id.'.xlsx');
    }
}