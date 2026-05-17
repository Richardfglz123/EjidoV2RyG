<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Programa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Postulacion;
use App\Models\usuario;

class ProgramaController extends Controller
{
    public function index()
    {
        $programas = Programa::whereNull('fecha_eliminado')
            ->orderBy('fecha_inicio', 'desc')
            ->get();


        $stats_disponibles = $programas->count();
        $stats_seleccionados = 0;
        $stats_periodo = \Carbon\Carbon::now()->year;


        return view('cpanel.Programas.programas', compact('programas', 'stats_disponibles', 'stats_seleccionados', 'stats_periodo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
        ]);

        Programa::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'estado' => 'Activo',
            'fecha_creo' => Carbon::now(),
            'id_creo' => Auth::id() ?? 1,
        ]);

        return redirect()->route('programas.index')
            ->with('success', 'Programa creado correctamente.');
    }

    public function obtenerPostulantes($id)
    {

        $postulantes = Postulacion::with(['usuario', 'evidencias'])
            ->where('id_programa', $id)
            ->get();

        $data = $postulantes->map(function($postulacion) {
            return [
                'id_postulacion' => $postulacion->id_postulacion,
                'nombre' => $postulacion->usuario->nombre . ' ' . $postulacion->usuario->apellido_paterno,
                'fecha' => \Carbon\Carbon::parse($postulacion->fecha_postulacion)->translatedFormat('d M Y'),
                'estado' => $postulacion->estado,
                'evidencias' => $postulacion->evidencias->map(function($img) {
                    return asset($img->ruta_imagen);
                })
            ];
        });

        return response()->json($data);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
        ]);

        $programa = Programa::findOrFail($id);

        $programa->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);

        return redirect()->route('programas.index')->with('success', 'Programa actualizado correctamente.');
    }

    public function destroy($id)
    {
        $programa = Programa::findOrFail($id);
        $programa->update([
            'fecha_eliminado' => \Carbon\Carbon::now(),
            'estado' => 'Eliminado'
        ]);

        return redirect()->route('programas.index')
            ->with('success', 'Programa eliminado correctamente.');
    }
}