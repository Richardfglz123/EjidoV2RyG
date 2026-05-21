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
            $id_referencia = $request->input('id_sesion');
            $qr_data = $request->input('qr_data');

            if (!$id_referencia || !$qr_data) {
                return response()->json(['success' => false, 'message' => "Faltan datos (ID: $id_referencia)"]);
            }

            $sesion = \App\Models\Sesion::firstOrCreate(
                [
                    'Tipo'          => 'Evento',
                    'Id_Referencia' => $id_referencia,
                    'Fecha'         => date('Y-m-d')
                ]
            );

            $raw = strtoupper($qr_data);

            $buscar = ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', 'Z', 'S', 'C'];
            $reemplazar = ['A', 'E', 'I', 'O', 'U', 'N', 'S', 'S', 'S'];
            $raw = str_replace($buscar, $reemplazar, $raw);

            $raw = preg_replace('/\([^)]+\)/', '', $raw);

            $raw = preg_replace('/[0-9.,-]/', ' ', $raw);

            $palabras = explode(' ', $raw);
            $palabrasLimpias = array_filter($palabras, function($p) {
                $p = trim($p);
                return $p !== '' && $p !== 'HERM' && strlen($p) > 1;
            });

            if (empty($palabrasLimpias)) {
                return response()->json(['success' => false, 'message' => "El QR no contiene un nombre reconocible."]);
            }

            $cadenaQR = implode(' ', $palabrasLimpias);

            $query = \Illuminate\Support\Facades\DB::table('Ejidatario as e')
                ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario');

            $concatBD = "REPLACE(REPLACE(REPLACE(UPPER(CONCAT_WS(' ', u.Nombres, u.Apellido_Paterno, u.Apellido_Materno)), 'Z', 'S'), 'C', 'S'), 'Ç', 'S')";

            $query->where(function($q) use ($palabrasLimpias, $concatBD) {
                foreach ($palabrasLimpias as $palabra) {
                    $q->orWhere(\Illuminate\Support\Facades\DB::raw($concatBD), 'LIKE', "%$palabra%");
                }
            });

            $candidatos = $query->select(
                'e.Id_Ejidatario',
                'e.Num_Ejidatario',
                'u.Nombres',
                'u.Apellido_Paterno',
                'u.Apellido_Materno',
                \Illuminate\Support\Facades\DB::raw("$concatBD as nombre_normalizado")
            )->get();

            $mejorMatch = null;
            $distanciaMinima = 999;

            foreach ($candidatos as $candidato) {
                $distancia = levenshtein($cadenaQR, $candidato->nombre_normalizado);

                if ($distancia < $distanciaMinima) {
                    $distanciaMinima = $distancia;
                    $mejorMatch = $candidato;
                }
            }

            if (!$mejorMatch || $distanciaMinima > 12) {
                return response()->json(['success' => false, 'message' => "No se encontró un usuario con suficiente similitud para: '$cadenaQR'"]);
            }

            $ejidatario = $mejorMatch;

            \Illuminate\Support\Facades\DB::table('PaseLista')->updateOrInsert(
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
                'num_ejid' => (int)$ejidatario->Num_Ejidatario,
                'nombre' => $ejidatario->Nombres . ' ' . $ejidatario->Apellido_Paterno . ' ' . $ejidatario->Apellido_Materno
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Error: " . $e->getMessage()
            ]);
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