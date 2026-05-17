<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ejidatario;
use App\Models\TipoUsoSuelo;
use App\Models\Parcela;
use App\Models\Coordenada;
use App\Models\Colindancia;
use App\Models\InfAdmin;

class ParcelaController extends Controller
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
            abort(403, 'No tienes permiso para gestionar parcelas.');
        }
    }

    public function index()
    {
        $parcelas = DB::table('Parcela as p')
            ->leftJoin('Ejidatario as e', 'e.Id_Ejidatario', '=', 'p.Id_Ejidatario')
            ->leftJoin('usuario as u', 'u.Id_usuario', '=', 'e.Id_usuario')
            ->select(
                'p.Id_Parcela',
                'p.No_Parcela as noParcela',
                'p.Ubicacion as ubicacion',
                DB::raw("CONCAT(u.Nombres,' ',u.Apellido_Paterno,' ',u.Apellido_Materno) as ejidatario")
            )
            ->orderBy('p.No_Parcela')
            ->get();

        return view('cpanel.ListViews.listadoParcelas', compact('parcelas'));
    }

    public function create(Request $request)
    {
        $this->checkPermission('usuarios_crear'); // Permiso para crear

        $Ejidatario = null;
        $error = null;

        if ($request->filled('numeroEjidatario')) {
            $Ejidatario = Ejidatario::where('Num_Ejidatario', $request->numeroEjidatario)->first();
            if (!$Ejidatario) {
                $error = 'No se encontró un ejidatario con ese número.';
            }
        }

        return view('cpanel.RegisterViews.nuevaParcela', [
            'Ejidatario' => $Ejidatario,
            'usos' => TipoUsoSuelo::all(),
            'error' => $error
        ]);
    }

    public function store(Request $request)
    {
        $this->checkPermission('usuarios_crear');

        DB::beginTransaction();
        try {
            $ejidatario = Ejidatario::where('Num_Ejidatario', $request->numeroEjidatario)->first();
            if (!$ejidatario) {
                return back()->with('status', 'error')->with('mensaje', 'Ejidatario no encontrado');
            }

            $parcela = Parcela::create([
                'No_Parcela'    => $request->noParcela,
                'Superficie'    => $request->superficie,
                'Ubicacion'     => $request->ubicacion,
                'Id_Ejidatario' => $ejidatario->Id_Ejidatario,
                'Id_Uso'        => $request->usoSuelo,
            ]);

            $parcela->colindancia()->create([
                'norte' => $request->norte, 'sur' => $request->sur, 'este' => $request->este,
                'oeste' => $request->oeste, 'noreste' => $request->noreste, 'noroeste' => $request->noroeste,
                'sureste' => $request->sureste, 'suroeste' => $request->suroeste,
            ]);

            if ($request->has('punto')) {
                foreach ($request->punto as $i => $p) {
                    if (!empty($request->coordenadaX[$i]) && !empty($request->coordenadaY[$i])) {
                        $parcela->coordenadas()->create([
                            'Punto'       => $p,
                            'CoordenadaX' => $request->coordenadaX[$i],
                            'CoordenadaY' => $request->coordenadaY[$i],
                        ]);
                    }
                }
            }

            $parcela->infAdmin()->create([
                'Num_InscripcionRAN' => $request->num_inscripcionRAN,
                'ClaveNucleoAgrario' => $request->claveNucleoAgrario,
                'Comunidad'          => $request->comunidad,
                'FechaExpedicion'    => $request->fechaExpedicion,
            ]);

            DB::commit();
            return redirect()->route('parcelas.index')->with('success', 'Parcela registrada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('status', 'error')->with('mensaje', $e->getMessage());
        }
    }

    public function verParcela(Request $request)
    {
        $parcela = Parcela::with(['ejidatario.usuario', 'colindancia', 'coordenadas', 'infAdmin', 'usoSuelo'])
            ->where('No_Parcela', $request->noParcela)
            ->first();

        if (!$parcela) {
            return back()->with('error', 'Parcela no encontrada.');
        }

        return view('cpanel.ListViews.verDetalleParcela', compact('parcela'));
    }

    public function editarParcela($id)
    {
        $this->checkPermission('usuarios_editar');

        $parcela = Parcela::with(['colindancia', 'coordenadas', 'infAdmin', 'ejidatario.usuario'])->findOrFail($id);
        $usos = TipoUsoSuelo::all();
        $todosLosEjidatarios = Ejidatario::with('usuario')->get();
        $Ejidatario = $parcela->ejidatario;

        return view('cpanel.EditViews.editarParcela', compact('parcela', 'usos', 'Ejidatario', 'todosLosEjidatarios'));
    }

    public function actualizarParcela(Request $request, $id)
    {
        $this->checkPermission('usuarios_editar');

        DB::beginTransaction();
        try {
            $parcela = Parcela::findOrFail($id);
            $parcela->update([
                'No_Parcela'    => $request->noParcela,
                'Superficie'    => $request->superficie,
                'Ubicacion'     => $request->ubicacion,
                'Id_Uso'        => $request->usoSuelo,
                'Id_Ejidatario' => $request->Id_Ejidatario,
            ]);

            $parcela->colindancia()->update([
                'norte' => $request->norte ?? '', 'sur' => $request->sur ?? '',
                'este'  => $request->este ?? '',  'oeste' => $request->oeste ?? '',
            ]);

            $parcela->coordenadas()->delete();
            if ($request->has('punto')) {
                foreach ($request->punto as $i => $p) {
                    if (!empty($request->coordenadaX[$i]) && !empty($request->coordenadaY[$i])) {
                        $parcela->coordenadas()->create([
                            'Punto'       => $p,
                            'CoordenadaX' => $request->coordenadaX[$i],
                            'CoordenadaY' => $request->coordenadaY[$i],
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('parcelas.index')->with('success', 'Información actualizada.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function eliminarParcela($id)
    {
        $this->checkPermission('usuarios_eliminar');
        try {
            $parcela = Parcela::findOrFail($id);
            $parcela->delete();
            return redirect()->route('parcelas.index')->with('success', 'Parcela eliminada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
}