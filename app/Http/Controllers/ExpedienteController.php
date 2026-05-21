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
        $permisos = session('usuario.permisos', []);
        $usuarioId = session('usuario.id');

        // Si tiene permiso puede ver todos los usuarios
        if (in_array('expedientes_ver', $permisos)) {

            $usuarios = Usuario::whereNull('fecha_eliminado')
                ->with('documentos')
                ->orderBy('Apellido_Paterno', 'asc')
                ->get();

        } else {

            // Si NO tiene permiso solo ve su propio expediente
            $usuarios = Usuario::where('Id_Usuario', $usuarioId)
                ->whereNull('fecha_eliminado')
                ->with('documentos')
                ->get();
        }

        $total_usuarios = $usuarios->count();
        $total_con_expediente = DocumentoUsuario::distinct('Id_Usuario')->count('Id_Usuario');

        return view(
            'cpanel.Expedientes.expediente',
            compact('usuarios', 'total_usuarios', 'total_con_expediente')
        );
    }

    public function store(Request $request)
    {
        // Validación básica
        $request->validate([
            'id_usuario' => 'required',
            'doc_ine' => 'nullable|file|mimes:pdf|max:5000',
            'doc_curp' => 'nullable|file|mimes:pdf|max:5000',
            'doc_comprobante' => 'nullable|file|mimes:pdf|max:5000',
        ]);

        $usuario = Usuario::findOrFail($request->id_usuario);
        $slugUsuario = Str::slug($usuario->Nombres . '-' . $usuario->Apellido_Paterno . '-' . $usuario->Id_Usuario);
        $rutaBase = "expedientes/{$slugUsuario}";

        $tipos = [
            'doc_ine' => 'INE',
            'doc_curp' => 'CURP',
            'doc_comprobante' => 'DOMICILIO'
        ];

        foreach ($tipos as $input => $nombreDoc) {
            if ($request->hasFile($input)) {
                // Guardamos en storage/app/public/expedientes/...
                $path = $request->file($input)->storeAs($rutaBase, $nombreDoc . '.pdf', 'public');

                DocumentoUsuario::updateOrCreate(
                    ['Id_Usuario' => $usuario->Id_Usuario, 'nombre_documento' => $nombreDoc],
                    ['ruta_archivo' => $path]
                );
            }
        }

        return redirect()->route('expedientes.index')->with('success', 'Expediente actualizado correctamente.');
    }

    public function guardarMio(Request $request)
    {
        $idUsuario = Auth::guard('ejidatario')->id();

        if(!$idUsuario) {
            return redirect()->back()->with('error', 'No se pudo identificar al usuario.');
        }

        $request->merge(['id_usuario' => $idUsuario]);
        return $this->store($request);
    }
}