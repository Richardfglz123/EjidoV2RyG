<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\DocumentoUsuario;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ExpedienteController extends Controller
{

    public function index()
    {
        $usuarios = Usuario::whereNull('fecha_eliminado')
            ->orderBy('apellido_paterno', 'asc')
            ->get();


        $total_usuarios = $usuarios->count();
        $total_con_expediente = DocumentoUsuario::count();


        return view('cpanel.Expedientes.expediente', compact('usuarios', 'total_usuarios', 'total_con_expediente'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'id_usuario' => 'required|exists:usuario,id_usuario',
            'doc_ine' => 'nullable|file|mimes:pdf|max:5000',
            'doc_curp' => 'nullable|file|mimes:pdf|max:5000',
            'doc_comprobante' => 'nullable|file|mimes:pdf|max:5000',
        ]);

        $usuario = Usuario::find($request->id_usuario);


        $slugUsuario = Str::slug($usuario->nombre . '-' . $usuario->apellido_paterno . '-' . $usuario->id_usuario);
        $rutaBase = "expedientes/{$slugUsuario}";

        if (!Storage::disk('public')->exists($rutaBase)) {
            Storage::disk('public')->makeDirectory($rutaBase);
        }


        $docs = DocumentoUsuario::firstOrNew(['id_usuario' => $usuario->id_usuario]);


        if($request->hasFile('doc_ine')) {
            $docs->ruta_ine = 'storage/' . $request->file('doc_ine')->storeAs($rutaBase, 'INE.'.$request->file('doc_ine')->extension(), 'public');
        }

        if($request->hasFile('doc_curp')) {
            $docs->ruta_curp = 'storage/' . $request->file('doc_curp')->storeAs($rutaBase, 'CURP.'.$request->file('doc_curp')->extension(), 'public');
        }

        if($request->hasFile('doc_comprobante')) {
            $docs->ruta_comprobante = 'storage/' . $request->file('doc_comprobante')->storeAs($rutaBase, 'COMPROBANTE.'.$request->file('doc_comprobante')->extension(), 'public');
        }

        $docs->save();

        return redirect()->back()->with('success', 'Expediente actualizado correctamente.');
    }

    public function guardarMio(Request $request)
    {

        $idUsuario = Auth::guard('ejidatario')->id();


        $request->merge(['id_usuario' => $idUsuario]);


        return $this->store($request);
    }
}