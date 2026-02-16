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
    public function index()
    {
        $parcelas = DB::table('Parcela as p')
            ->leftJoin('Ejidatario as e', 'e.Id_Ejidatario', '=', 'p.Id_Ejidatario')
            ->leftJoin('Usuario as u', 'u.Id_Usuario', '=', 'e.Id_Usuario')
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
        $Ejidatario = null;
        $error = null;

        if ($request->filled('numeroEjidatario')) {
            $Ejidatario = Ejidatario::where(
                'Num_Ejidatario',
                $request->numeroEjidatario
            )->first();

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
                'norte'    => $request->norte, 'sur' => $request->sur, 'este' => $request->este,
                'oeste'    => $request->oeste, 'noreste' => $request->noreste, 'noroeste' => $request->noroeste,
                'sureste'  => $request->sureste, 'suroeste' => $request->suroeste,
            ]);

            foreach ($request->punto as $i => $p) {
                if (!empty($request->coordenadaX[$i]) && !empty($request->coordenadaY[$i])) {
                    $parcela->coordenadas()->create([
                        'Punto'        => $p,
                        'CoordenadaX'  => $request->coordenadaX[$i],
                        'CoordenadaY'  => $request->coordenadaY[$i],
                    ]);
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
        $parcela = Parcela::with(['ejidatario.usuario', 'colindancia', 'coordenadas', 'infAdmin'])
            ->where('No_Parcela', $request->noParcela)
            ->first();

        if (!$parcela) {
            return back()->with('error', 'Parcela no encontrada.');
        }

        $usos = TipoUsoSuelo::all();
        $todosLosEjidatarios = Ejidatario::with('usuario')->get();
        $Ejidatario = $parcela->ejidatario;

        return view('cpanel.EditViews.editarParcela', compact('parcela', 'usos', 'Ejidatario', 'todosLosEjidatarios'));
    }

    public function editarParcela($id)
    {
        $parcela = Parcela::with(['colindancia', 'coordenadas', 'infAdmin', 'ejidatario.usuario'])->findOrFail($id);
        $usos = TipoUsoSuelo::all();

        // Se cargan todos los ejidatarios para que el buscador de Select2 tenga datos
        $todosLosEjidatarios = Ejidatario::with('usuario')->get();
        $Ejidatario = $parcela->ejidatario;

        return view('cpanel.EditViews.editarParcela', compact('parcela', 'usos', 'Ejidatario', 'todosLosEjidatarios'));
    }

    public function eliminarParcela($id)
    {
        try {
            $parcela = Parcela::findOrFail($id);
            $parcela->delete();
            return redirect()->route('parcelas.index')->with('success', 'Parcela eliminada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function actualizarParcela(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $parcela = Parcela::findOrFail($id);

            // Actualizar datos y el nuevo ID del Ejidatario seleccionado en el buscador
            $parcela->update([
                'No_Parcela'    => $request->noParcela,
                'Superficie'    => $request->superficie,
                'Ubicacion'     => $request->ubicacion,
                'Id_Uso'        => $request->usoSuelo,
                'Id_Ejidatario' => $request->Id_Ejidatario,
            ]);

            $parcela->colindancia()->update([
                'norte'    => $request->norte ?? '',
                'sur'      => $request->sur ?? '',
                'este'     => $request->este ?? '',
                'oeste'    => $request->oeste ?? '',
                'noreste'  => $request->noreste ?? '',
                'noroeste' => $request->noroeste ?? '',
                'sureste'  => $request->sureste ?? '',
                'suroeste' => $request->suroeste ?? '',
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

            $parcela->infAdmin()->update([
                'Num_InscripcionRAN' => $request->num_inscripcionRAN ?? '',
                'ClaveNucleoAgrario' => $request->claveNucleoAgrario ?? '',
                'Comunidad'          => $request->comunidad ?? '',
                'FechaExpedicion'    => $request->fechaExpedicion,
            ]);

            DB::commit();
            return redirect()->route('parcelas.index')->with('success', 'Información actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }
}