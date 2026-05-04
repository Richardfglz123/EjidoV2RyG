<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Categoria_Evento;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    public function index(Request $request)
    {
        $nombre = $request->get('nombreEvento');
        $categoria = $request->get('categoria');

        // 2. Realizamos la consulta.
        // Usamos 'data' para que coincida exactamente con tu vista Blade.
        $data = Evento::when($nombre, function ($query, $nombre) {
            return $query->where('Nombre_Evento', 'LIKE', "%$nombre%");
        })
            ->when($categoria, function ($query, $categoria) {
                return $query->where('Id_Categoria_Evento', $categoria);
            })
            ->paginate(10); // Importante para que el método ->total() funcione

        // 3. Retornamos la vista enviando la variable $data
        return view('cpanel.Evento.indexEvento', compact('data'));
    }
    public function create()
    {
        $categorias = Categoria_Evento::all();
        // Debe apuntar a la vista del FORMULARIO, no a la del LISTADO
        return view('cpanel.Evento.crearEvento', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Nombre_Evento' => 'required|string|max:100',
            'Id_Categoria_Evento' => 'required|exists:Categoria_Evento,Id_Categoria_Evento',
        ]);

        Evento::create([
            'Nombre_Evento' => $request->Nombre_Evento,
            'Id_Categoria_Evento' => $request->Id_Categoria_Evento,
            'Observaciones' => $request->Observaciones,
            'Id_Creo' => auth()->user()->username ?? 'lou',
            'Fecha_Creo' => now(),
        ]);

        return redirect()->route('eventos.index')->with('success', 'Evento guardado');
    }
}