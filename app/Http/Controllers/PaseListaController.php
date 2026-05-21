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
            ->select('e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno', 'a.Fecha as Hora')
            ->orderBy('a.Fecha', 'desc')
            ->get();

        $evento = Evento::find($request->id_referencia);
        return view('cpanel.PaseLista.escanear', compact('sesion', 'evento', 'presentes'));
    }

    public function marcarAsistencia(Request $request)
    {
        try {
            $id_sesion = $request->input('id_sesion');
            $qr_data = $request->input('qr_data');

            // 1. Validar que el ID llegue
            if (!$id_sesion || !$qr_data) {
                return response()->json(['success' => false, 'message' => "Datos incompletos"]);
            }

            // 2. Buscar la sesión por el ID recibido
            $sesion = Sesion::find($id_sesion);
            if (!$sesion) {
                return response()->json(['success' => false, 'message' => "Sesión no encontrada (ID: $id_sesion)"]);
            }

            // 3. Lógica de limpieza QR
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

            // 4. Registro único: Si el registro ya existe, no hace nada, si no, lo inserta.
            DB::table('PaseLista')->updateOrInsert(
                ['Id_Sesion' => $sesion->Id_Sesion, 'Id_Ejidatario' => $mejorMatch->Id_Ejidatario],
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

        // 1. Obtener IDs de quienes SÍ asistieron
        $idsAsistentes = DB::table('PaseLista')->where('Id_Sesion', $id)->pluck('Id_Ejidatario');

        // 2. Lista de asistentes
        $asistentes = DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->whereIn('e.Id_Ejidatario', $idsAsistentes)
            ->select('e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno')
            ->get();

        // 3. Lista de quienes NO asistieron (AQUÍ ESTABA EL ERROR DEL NULL)
        $noAsistieron = DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->whereNotIn('e.Id_Ejidatario', $idsAsistentes)
            ->select('e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno')
            ->get();

        // 4. Total general
        $total = Ejidatario::count();

        // Enviamos todo lo que tu vista espera
        return Pdf::loadView('cpanel.PaseLista.asistenciapdf', compact('sesion', 'asistentes', 'noAsistieron', 'total'))
            ->stream('Reporte_Asistencia_'.$id.'.pdf');
    }

    public function exportarExcel($id)
    {
        return Excel::download(new AsistenciaExport($id), 'Asistencia_'.$id.'.xlsx');
    }
}