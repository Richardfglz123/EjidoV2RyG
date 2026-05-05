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
        $request->validate([
            'Nombre_Evento' => 'required|string|max:100',
            'Id_Categoria_Evento' => 'required|exists:Categoria_Evento,Id_Categoria_Evento',
            'Observaciones' => 'nullable|string',
        ]);

        Evento::create([
            'Nombre_Evento'       => $request->Nombre_Evento,
            'Id_Categoria_Evento' => $request->Id_Categoria_Evento,
            'Observaciones'       => $request->Observaciones,
            'Id_Creo'             => auth()->user()->username ?? 'admin',
            'Fecha_Creo'          => now(),
        ]);

        return redirect()->route('eventos.index')->with('success', 'Evento guardado correctamente');
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
        $evento->delete();

        return redirect()->route('eventos.index')->with('success', 'Evento eliminado con éxito.');
    }
}