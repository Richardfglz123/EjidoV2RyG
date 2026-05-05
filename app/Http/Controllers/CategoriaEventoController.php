<?php

namespace App\Http\Controllers;

use App\Models\CategoriaEvento;
use Illuminate\Http\Request;
class CategoriaEventoController extends Controller
{
    public function index()
    {
        $categorias = CategoriaEvento::all();
        return view('cpanel.Evento.Categorias', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Nombre_Categoria' => 'required|max:255'
        ]);

        CategoriaEvento::create($request->all());

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría creada con éxito');
    }

    public function destroy($id)
    {
        CategoriaEvento::destroy($id);
        return redirect()->route('categorias.index')
            ->with('success', 'Categoría eliminada');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'Nombre_Categoria' => 'required|max:255'
        ]);

        $categoria = CategoriaEvento::findOrFail($id);
        $categoria->update([
            'Nombre_Categoria' => $request->Nombre_Categoria
        ]);

        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada');
    }
}