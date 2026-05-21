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

        $sesion = Sesion::firstOrCreate(
            [
                'Tipo'          => $request->tipo,
                'Id_Referencia' => $request->id_referencia,
                'Fecha'         => $request->fecha
            ]
        );

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
            $id_evento = $request->input('id_sesion'); // ID del evento
            $qr_data = $request->input('qr_data');

            if (!$id_evento || !$qr_data) {
                return response()->json(['success' => false, 'message' => "Datos incompletos"]);
            }

            // 1. BUSCAR SESIÓN EXISTENTE PARA EL EVENTO
            // No creamos con date('Y-m-d') porque si el evento es de ayer, se crea una sesión nueva.
            // Buscamos la sesión vinculada a este evento.
            $sesion = \App\Models\Sesion::where('Id_Referencia', $id_evento)
                ->where('Tipo', 'Evento')
                ->first();

            // Si no existe ninguna sesión, la creamos (solo una vez por evento)
            if (!$sesion) {
                $sesion = \App\Models\Sesion::create([
                    'Tipo'          => 'Evento',
                    'Id_Referencia' => $id_evento,
                    'Fecha'         => now()
                ]);
            }

            // 2. LÓGICA DE LIMPIEZA DE QR (Igual a la que ya tienes)
            $raw = strtoupper(preg_replace('/[0-9.,-]/', ' ', preg_replace('/\([^)]+\)/', '', str_replace(['Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', 'Z', 'S', 'C'], ['A', 'E', 'I', 'O', 'U', 'N', 'S', 'S', 'S'], $qr_data))));
            $palabrasLimpias = array_filter(explode(' ', $raw), fn($p) => strlen(trim($p)) > 1 && $p !== 'HERM');

            if (empty($palabrasLimpias)) return response()->json(['success' => false, 'message' => "QR inválido"]);

            // 3. BÚSQUEDA DEL EJIDATARIO
            $concatBD = "REPLACE(REPLACE(REPLACE(UPPER(CONCAT_WS(' ', u.Nombres, u.Apellido_Paterno, u.Apellido_Materno)), 'Z', 'S'), 'C', 'S'), 'Ç', 'S')";

            $candidatos = \Illuminate\Support\Facades\DB::table('Ejidatario as e')
                ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
                ->select('e.Id_Ejidatario', 'e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno', \Illuminate\Support\Facades\DB::raw("$concatBD as nombre_normalizado"))
                ->get();

            $mejorMatch = null;
            $distanciaMinima = 999;
            $cadenaQR = implode(' ', $palabrasLimpias);

            foreach ($candidatos as $c) {
                $distancia = levenshtein($cadenaQR, $c->nombre_normalizado);
                if ($distancia < $distanciaMinima) {
                    $distanciaMinima = $distancia;
                    $mejorMatch = $c;
                }
            }

            if (!$mejorMatch || $distanciaMinima > 12) {
                return response()->json(['success' => false, 'message' => "Usuario no encontrado"]);
            }

            // 4. REGISTRO DE ASISTENCIA
            // Guardamos el Id_Sesion real (el que encontramos o creamos al inicio)
            \Illuminate\Support\Facades\DB::table('PaseLista')->updateOrInsert(
                [
                    'Id_Sesion' => $sesion->Id_Sesion,
                    'Id_Ejidatario' => $mejorMatch->Id_Ejidatario
                ],
                [
                    'Asistencia'   => 1,
                    'Fecha'        => now(),
                    'Id_Actividad' => null // Dejamos en null si el reporte lee de la tabla Sesion
                ]
            );

            return response()->json([
                'success' => true,
                'nombre'  => $mejorMatch->Nombres . ' ' . $mejorMatch->Apellido_Paterno
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
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

        $asistieron = DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->join('PaseLista as p', 'e.Id_Ejidatario', '=', 'p.Id_Ejidatario')
            ->where('p.Id_Sesion', $id)
            ->select('e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno')
            ->get();

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
    public function exportarExcel($id)
    {

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AsistenciaExport($id),
            'Reporte_Asistencia_' . $id . '.xlsx'
        );
    }
}