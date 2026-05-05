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
class PaseListaController extends Controller
{
    public function index()
    {
        $eventos = Evento::all();
        $totalEjidatarios = \App\Models\Ejidatario::count();

        $sesiones = Sesion::with('evento')
            ->withCount('asistencias')
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

        $evento = Evento::find($request->id_referencia);

        return view('cpanel.PaseLista.escanear', compact('sesion', 'evento'));
    }
    public function marcarAsistencia(Request $request)
    {
        $ejidatario = Ejidatario::find($request->id_ejidatario);

        if($ejidatario) {
            $asistencia = PaseLista::updateOrCreate(
                [
                    'Id_Sesion' => $request->id_sesion,
                    'Id_Ejidatario' => $ejidatario->Id_Ejidatario
                ],
                [
                    'Estatus' => 'Presente',
                    'Fecha_Creo' => now()
                ]
            );

            return response()->json([
                'status' => 'success',
                'nombre' => $ejidatario->Nombre_Completo
            ]);
        }

        return response()->json(['status' => 'error'], 404);
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
        return Excel::download(new AsistenciaExport($id), 'asistencia_evento_'.$id.'.xlsx');
    }

    public function exportarPdf($id)
    {
        $sesion = Sesion::with('evento')->findOrFail($id);

        $idsAsistentes = PaseLista::where('Id_Sesion', $id)->pluck('Id_Ejidatario')->toArray();
        $asistieron = Ejidatario::whereIn('Id_Ejidatario', $idsAsistentes)->get();
        $noAsistieron = Ejidatario::whereNotIn('Id_Ejidatario', $idsAsistentes)->get();
        $total = Ejidatario::count();

        $pdf = Pdf::loadView('cpanel.PaseLista.asistenciapdf', compact('sesion', 'asistieron', 'noAsistieron', 'total'));
        return $pdf->stream('Reporte_Completo_Sesion_'.$id.'.pdf');
    }
}