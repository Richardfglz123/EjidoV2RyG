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
        try {
            $id_sesion = $request->input('id_sesion'); // Asegúrate que el iPhone envíe este nombre
            $qr_data   = $request->input('qr_data');

            if (!$id_sesion || !$qr_data) {
                return response()->json(['success' => false, 'message' => "Datos incompletos"]);
            }

            // 1. Buscar Ejidatario con coincidencia mejorada
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

            // Si la distancia es mayor a 20, no arriesgamos el registro
            if (!$mejorMatch || levenshtein($cadenaQR, strtoupper($mejorMatch->Nombres . ' ' . $mejorMatch->Apellido_Paterno . ' ' . $mejorMatch->Apellido_Materno)) > 20) {
                return response()->json(['success' => false, 'message' => "Ejidatario no identificado"]);
            }

            // 2. Registrar asistencia sin borrar (usamos updateOrInsert)
            DB::table('PaseLista')->updateOrInsert(
                [
                    'Id_Sesion'     => (int)$id_sesion,
                    'Id_Ejidatario' => $mejorMatch->Id_Ejidatario
                ],
                [
                    'Asistencia' => 1,
                    'Fecha'      => now()->format('Y-m-d H:i:s')
                ]
            );

            return response()->json([
                'success'  => true,
                'nombre'   => $mejorMatch->Nombres . ' ' . $mejorMatch->Apellido_Paterno
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
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