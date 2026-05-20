<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Categoria_Evento;
use Illuminate\Http\Request;
use App\Models\Sesion;
use Illuminate\Support\Facades\DB;

class EventoController extends Controller
{
    public function index(Request $request)
    {
        $nombre = $request->get('nombreEvento');
        $categoria = $request->get('categoria');

        $data = Evento::when($nombre, function ($query, $nombre) {
            return $query->where('Nombre_Evento', 'LIKE', "%$nombre%");
        })
            ->when($categoria, function ($query, $categoria) {
                return $query->where('Id_Categoria_Evento', $categoria);
            })
            ->paginate(10);

        return view('cpanel.Evento.indexEvento', compact('data'));
    }
    public function create()
    {
        $categorias = Categoria_Evento::all();
        return view('cpanel.Evento.crearEvento', compact('categorias'));
    }

    public function store(Request $request)
    {
        // Validamos los campos que vimos en tu captura de pantalla
        $request->validate([
            'Nombre_Evento'       => 'required|string|max:100',
            'Id_Categoria_Evento' => 'required',
            'Observaciones'       => 'nullable|string',
        ]);

        try {
            $evento = Evento::create([
                'Nombre_Evento'       => $request->Nombre_Evento,
                'Id_Categoria_Evento' => $request->Id_Categoria_Evento, // El ID numérico (1, 2, 3...)
                'Observaciones'       => $request->Observaciones,
                'Id_Creo'             => auth()->check() ? auth()->user()->username : 'iPhone_App',
                'Fecha_Creo'          => now(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'evento' => $evento]);
            }

            return redirect()->route('eventos.index')->with('success', 'Evento guardado');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function edit($id)
    {
        $evento = Evento::findOrFail($id);

        $categorias = Categoria_Evento::all();

        return view('cpanel.Evento.editEvento', compact('evento', 'categorias'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'Nombre_Evento'       => 'required|string|max:100',
            'Id_Categoria_Evento' => 'required',
            'Observaciones'       => 'nullable|string',
        ]);

        $evento = Evento::findOrFail($id);
        $evento->update([
            'Nombre_Evento'       => $request->Nombre_Evento,
            'Id_Categoria_Evento' => $request->Id_Categoria_Evento,
            'Observaciones'       => $request->Observaciones,
            'Id_Modificado'       => auth()->user()->username ?? 'admin',
            'Fecha_Modificado'    => now(),
        ]);

        return redirect()->route('eventos.index')->with('success', 'Evento actualizado correctamente.');
    }

    public function destroy($id)
    {
        $evento = Evento::findOrFail($id);
        $sesiones = Sesion::where('Id_Referencia', $id)
            ->where('Tipo', 'Evento')
            ->get();

        foreach($sesiones as $sesion) {
            DB::table('PaseLista')->where('Id_Sesion', $sesion->Id_Sesion)->delete();
            $sesion->delete();
        }
        $evento->delete();

        return redirect()->route('eventos.index')->with('success', 'Evento y sus registros de Pase de Lista eliminados correctamente.');
    }
    public function storeApi(Request $request)
    {
        // Validamos los datos EXACTOS
        $validated = $request->validate([
            'Nombre_Evento'       => 'required|string|max:100',
            'Id_Categoria_Evento' => 'required|integer',
            'Observaciones'       => 'nullable|string',
        ]);

        try {
            $evento = Evento::create([
                'Nombre_Evento'       => $request->Nombre_Evento,
                'Id_Categoria_Evento' => $request->Id_Categoria_Evento,
                'Observaciones'       => $request->Observaciones,
                'Id_Creo'             => 'App_iOS', // Evitamos el error de Id_Creo nulo
                'Fecha_Creo'          => now(),
            ]);

            return response()->json(['success' => true, 'evento' => $evento]);

        } catch (\Exception $e) {
            // Esto te dirá en el log si falta alguna columna o el ID de categoría no existe
            return response()->json([
                'success' => false,
                'message' => "Error de base de datos: " . $e->getMessage()
            ], 500);
        }
    }
}