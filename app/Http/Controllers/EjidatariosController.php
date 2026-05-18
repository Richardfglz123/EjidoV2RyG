<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EjidatariosController extends Controller
{
    private function checkPermission($permission)
    {
        $sesion = session('usuario', session('2fa_user', []));
        $permisos = $sesion['permisos'] ?? [];
        $rol = strtolower(trim($sesion['rol'] ?? ''));

        if ($rol === 'administrador' || ($sesion['id_rol'] ?? null) == 2) {
            return true;
        }

        if (!in_array($permission, $permisos)) {
            abort(403, 'No tienes permiso para gestionar ejidatarios.');
        }
    }

    public function index()
    {
        $ejidatarios = DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->join('Estatus as es', 'e.Id_Estatus', '=', 'es.Id_Estatus')
            ->select(
                'e.*',
                'u.Nombres',
                'u.Apellido_Paterno',
                'u.Apellido_Materno',
                'es.Estatus as NombreEstatus'
            )
            ->orderByRaw('e.Num_Ejidatario + 0 ASC')
            ->paginate(10);

        return view('cpanel/ejidatarios/indexEjidatario', [
            'data' => $ejidatarios
        ]);
    }

    public function create()
    {
        $this->checkPermission('usuarios_crear');

        $usuarios = DB::table('usuario')->get();
        $estatus  = DB::table('Estatus')->get();

        return view('cpanel.ejidatarios.CrearEjidatario', [
            'usuarios' => $usuarios,
            'estatus'  => $estatus
        ]);
    }

    public function store(Request $request)
    {
        $this->checkPermission('usuarios_crear');

        // 1. Validamos (Asegúrate de que los nombres coincidan con el <select> de tu vista)
        $request->validate([
            'Calle'              => 'required|string|max:100',
            'Num_Exterior'       => 'required|string|max:10',
            'Colonia'            => 'required|string|max:100',
            'Municipio'          => 'required|string|max:100',
            'Estado'             => 'required|string|max:100',
            'Codigo_Postal'      => 'required|string|max:10',
            'Fecha_Nacimiento'   => 'required|date',
            'CURP'               => 'required|string|max:20|unique:Ejidatario,CURP',
            'RFC'                => 'required|string|max:15',
            'Clave_Elector'      => 'required|string|max:20',
            'Fecha_Ingreso'      => 'required|date',
            'Id_Estatus'         => 'required|exists:Estatus,Id_Estatus',
            'Id_usuario'         => 'required', // Este es el nombre del input en el formulario
        ]);

        // 2. Generar Número de Ejidatario automático
        $ultimoNum = DB::table('Ejidatario')->max('Num_Ejidatario');
        $nuevoNum = $ultimoNum ? ($ultimoNum + 1) : 1;

        // 3. Preparar QR (Buscamos al usuario usando Id_Usuario con U mayúscula)
        $user = DB::table('usuario')->where('Id_Usuario', $request->Id_usuario)->first();

        if (!$user) {
            return back()->withErrors('El usuario seleccionado no existe en la tabla usuarios.');
        }

        $payloadQR = strtoupper(trim($user->Nombres . ' ' . $user->Apellido_Paterno . ' ' . $user->Apellido_Materno));
        $payloadQR = preg_replace('/\s+/', ' ', $payloadQR);

        try {
            // 4. Inserción (Usando los nombres EXACTOS de tu estructura de tabla)
            DB::table('Ejidatario')->insert([
                'Num_Ejidatario'   => $nuevoNum,
                'Id_Usuario'       => $request->Id_usuario, // CORREGIDO: U mayúscula
                'qr_payload'       => $payloadQR,
                'Calle'            => $request->Calle,
                'Num_Exterior'     => $request->Num_Exterior,
                'Num_Interior'     => $request->Num_Interior,
                'Colonia'          => $request->Colonia,
                'Municipio'        => $request->Municipio,
                'Estado'           => $request->Estado,
                'Codigo_Postal'    => $request->Codigo_Postal,
                'Fecha_Nacimiento' => $request->Fecha_Nacimiento,
                'CURP'             => $request->CURP,
                'RFC'              => $request->RFC,
                'Clave_Elector'    => $request->Clave_Elector,
                'Fecha_Ingreso'    => $request->Fecha_Ingreso,
                'Id_Estatus'       => $request->Id_Estatus,
                'Fecha_Creo'       => now()->format('Y-m-d'), // Tu tabla pide DATE, no DATETIME
                'Id_Creo'          => session('usuario.id', '1')
            ]);

            return redirect()->route('Ejidatarios.index')->with('success', "Ejidatario #{$nuevoNum} registrado con éxito.");

        } catch (\Exception $e) {
            // Esto te mostrará el error real en pantalla si algo falla
            return back()->withInput()->withErrors('Error al insertar: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $this->checkPermission('usuarios_editar');

        $fila = DB::table('Ejidatario')->where('Id_Ejidatario', $id)->first();
        abort_if(!$fila, 404);

        $usuarios = DB::table('usuario')->get();
        $estatus  = DB::table('Estatus')->get();

        return view('cpanel/ejidatarios/editEjidatarios', compact('fila', 'usuarios', 'estatus'));
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('usuarios_editar');

        $request->validate([
            'Num_Ejidatario'   => 'required|integer|unique:Ejidatario,Num_Ejidatario,' . $id . ',Id_Ejidatario',
            'CURP'             => 'required|string|max:20|unique:Ejidatario,CURP,' . $id . ',Id_Ejidatario',
        ]);

        DB::table('Ejidatario')->where('Id_Ejidatario', $id)->update([
            'Num_Ejidatario'   => $request->Num_Ejidatario,
            'Calle'            => $request->Calle,
            'Num_Exterior'     => $request->Num_Exterior,
            'Num_Interior'     => $request->Num_Interior,
            'Colonia'          => $request->Colonia,
            'Municipio'        => $request->Municipio,
            'Estado'           => $request->Estado,
            'Codigo_Postal'    => $request->Codigo_Postal,
            'Fecha_Nacimiento' => $request->Fecha_Nacimiento,
            'CURP'             => $request->CURP,
            'RFC'              => $request->RFC,
            'Clave_Elector'    => $request->Clave_Elector,
            'Fecha_Ingreso'    => $request->Fecha_Ingreso,
            'Id_Estatus'       => $request->Id_Estatus,
            'Id_usuario'       => $request->Id_usuario,
            'Fecha_Modificado' => now(),
            'Id_Modificado'    => session('usuario.username', 'admin')
        ]);

        return redirect()->route('Ejidatarios.index')->with('success', 'Ejidatario actualizado');
    }

    public function destroy($id)
    {
        $sesion = session('usuario', session('2fa_user', []));
        $miId = $sesion['id'] ?? null;
        $miRol = strtolower(trim($sesion['rol'] ?? ''));
        $esAdmin = ($miRol === 'administrador' || ($sesion['id_rol'] ?? null) == 2);

        if (!$esAdmin && !in_array('usuarios_eliminar', $sesion['permisos'] ?? [])) {
            abort(403, 'No tienes permiso para eliminar.');
        }

        $fila = DB::table('Ejidatario')->where('Id_Ejidatario', $id)->first();

        if (!$fila) {
            return back()->withErrors('Ejidatario no encontrado.');
        }

        // Permitir eliminar solo si no soy yo O si soy administrador
        if (!$esAdmin && $miId == $fila->Id_usuario) {
            return back()->withErrors('No puedes eliminar tu propio registro.');
        }

        try {
            DB::table('Ejidatario')->where('Id_Ejidatario', $id)->delete();
            return back()->with('success', 'Registro eliminado correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors('Error al eliminar: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $monto = DB::table('Reparto')->where('id_ejidatario', $id)->value('monto') ?? 0;
        return response()->json(['saldo_disponible' => $monto]);
    }

    public function buscarCP($cp)
    {
        // Buscamos en la tabla de sepomex
        $resultados = DB::table('sepomex')
            ->where('codigo_postal', $cp)
            ->select('colonia', 'municipio', 'estado')
            ->get();

        return response()->json($resultados);
    }
    public function getEjidatariosApi() {
        $ejidatarios = DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->join('Estatus as es', 'e.Id_Estatus', '=', 'es.Id_Estatus')
            ->select(
                'e.Id_Ejidatario',
                'e.Num_Ejidatario',
                'u.Nombres',
                'u.Apellido_Paterno',
                'u.Apellido_Materno',
                'e.qr_payload',
                'es.Estatus as NombreEstatus' // Importante: darle el nombre exacto
            )
            ->get();

        return response()->json($ejidatarios);
    }
}