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
            ->with('documentos')
            ->orderBy('Apellido_Paterno', 'asc')
            ->get();

        // Transformamos los documentos para que tu JS los lea fácil
        foreach ($usuarios as $u) {
            $u->ruta_ine = $u->documentos->where('nombre_documento', 'INE')->first()->ruta_archivo ?? '';
            $u->ruta_curp = $u->documentos->where('nombre_documento', 'CURP')->first()->ruta_archivo ?? '';
            $u->ruta_comp = $u->documentos->where('nombre_documento', 'DOMICILIO')->first()->ruta_archivo ?? '';
        }

        $total_usuarios = $usuarios->count();
        $total_con_expediente = \App\Models\DocumentoUsuario::distinct('Id_Usuario')->count('Id_Usuario');

        return view('cpanel.Expedientes.expediente', compact('usuarios', 'total_usuarios', 'total_con_expediente'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required',
            'doc_ine' => 'nullable|file|mimes:pdf|max:5000',
            'doc_curp' => 'nullable|file|mimes:pdf|max:5000',
            'doc_comprobante' => 'nullable|file|mimes:pdf|max:5000',
        ]);

        $usuario = Usuario::findOrFail($request->id_usuario);
        $slugusuario = Str::slug($usuario->Nombres . '-' . $usuario->Apellido_Paterno . '-' . $usuario->Id_usuario);
        $rutaBase = "expedientes/{$slugusuario}";

        $tipos = [
            'doc_ine' => 'INE',
            'doc_curp' => 'CURP',
            'doc_comprobante' => 'DOMICILIO'
        ];

        foreach ($tipos as $input => $nombreDoc) {
            if ($request->hasFile($input)) {
                $path = $request->file($input)->storeAs($rutaBase, $nombreDoc . '.pdf', 'public');

                $idParaInsertar = $usuario->Id_Usuario ?? $usuario->Id_usuario ?? $usuario->id;

                DocumentoUsuario::updateOrCreate(
                    [
                        'Id_Usuario'       => $idParaInsertar,
                        'nombre_documento' => $nombreDoc
                    ],
                    [
                        'ruta_archivo'     => $path,
                        'Id_Creo'          => session('usuario.id'),
                        'Id_Modificado'    => session('usuario.id'),
                    ]
                );
            }
        }

        return redirect()->route('expedientes.index')->with('success', 'Expediente actualizado correctamente.');
    }

    public function guardarMio(Request $request)
    {
        $idusuario = Auth::guard('ejidatario')->id();

        if(!$idusuario) {
            return redirect()->back()->with('error', 'No se pudo identificar al usuario.');
        }

        $request->merge(['id_usuario' => $idusuario]);
        return $this->store($request);
    }
}