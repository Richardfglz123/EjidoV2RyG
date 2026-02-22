@extends('cpanel/plantilla')

@section('title', 'Gestión de Programas')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-hand-holding-heart me-2"></i> Programas de Apoyo
        </h1>
    </div>

    <div class="card card-ejidal mb-4 shadow-sm">
        <div class="card-header card-header-ejidal font-weight-bold">
            <i class="fas fa-plus-circle me-2"></i> Registro de Nuevo Programa
        </div>

        <form id="form-programa" action="{{ route('programas.store') }}" method="POST">
            @csrf
            <input type="hidden" id="id_programa">

            <div class="card-body">

                <div class="row">
                    <div class="col-md-8">
                        <div class="row mb-3">

                            <div class="col-md-12 mb-3">
                                <label class="fw-bold">Nombre del Programa</label>
                                <input type="text" name="nombre" id="nombre"
                                       class="form-control"
                                       required>
                            </div>

                            <div class="col-md-12">
                                <label class="fw-bold">Descripción del Programa</label>
                                <textarea name="descripcion" id="descripcion" rows="4"
                                          class="form-control"></textarea>
                            </div>

                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-light border-0 p-3">
                            <div class="mb-3">
                                <label class="fw-bold text-ejidal">Fecha de Inicio</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio"
                                       class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold text-ejidal">Fecha de Fin</label>
                                <input type="date" name="fecha_fin" id="fecha_fin"
                                       class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end border-top pt-3 mt-2">
                    <button type="button" onclick="resetForm()" class="btn btn-outline-secondary me-2">
                        Limpiar
                    </button>

                    <button type="submit" id="btn-submit" class="btn btn-ejidal px-4">
                        Guardar Programa
                    </button>
                </div>

            </div>
        </form>
    </div>

    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal">
            Programas Registrados
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Programa</th>
                    <th>Vigencia</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>

                <tbody>
                @forelse ($programas as $prog)
                    <tr>
                        <td>{{ $prog->nombre }}</td>
                        <td>{{ $prog->fecha_inicio }} / {{ $prog->fecha_fin }}</td>
                        <td>{{ $prog->estado }}</td>
                        <td>

                            <button type="button"
                                    class="btn btn-warning btn-sm"
                                    onclick='editarProg({
            id: {{ $prog->id }},
            nombre: @json($prog->nombre),
            descripcion: @json($prog->descripcion),
            fecha_inicio: @json($prog->fecha_inicio),
            fecha_fin: @json($prog->fecha_fin)
        })'>
                                Editar
                            </button>

                            <form action="{{ url('/admon/programas/'.$prog->id) }}"
                                  method="POST"
                                  class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">
                                    Eliminar
                                </button>
                            </form>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No hay registros</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection


{{-- SCRIPT DIRECTO (NO DEPENDE DE YIELD) --}}
<script>

    function editarProg(programa) {
        const form = document.getElementById('form-programa');

        // Usar directamente el ID del objeto
        form.action = `/admon/programas/${programa.id}`;

        document.getElementById('nombre').value = programa.nombre || '';
        document.getElementById('descripcion').value = programa.descripcion || '';
        document.getElementById('fecha_inicio').value = programa.fecha_inicio || '';
        document.getElementById('fecha_fin').value = programa.fecha_fin || '';

        // Crear o actualizar campo _method para PUT
        let methodInput = document.getElementById('method_put');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.id = 'method_put';
            form.appendChild(methodInput);
        }
        methodInput.value = 'PUT';

        document.getElementById('btn-submit').innerText = "Actualizar Programa";

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }


    function resetForm() {

        const form = document.getElementById('form-programa');

        form.reset();
        form.action = "{{ route('programas.store') }}";

        const methodInput = document.getElementById('method_put');
        if (methodInput) {
            methodInput.remove();
        }

        document.getElementById('btn-submit').innerText = "Guardar Programa";
    }

</script>