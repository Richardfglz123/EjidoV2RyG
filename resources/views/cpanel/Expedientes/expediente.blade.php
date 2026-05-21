@extends('cpanel.plantilla')
@section('title', 'Ejidatarios')

@section('content')

<div class="card card-ejidal mb-4">
    <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
        <span><i class="fas fa-users me-2"></i> Ejidatarios</span>
        <a href="{{ route('ejidatarios.create') }}" class="btn btn-ejidal text-white">
            <i class="fas fa-plus me-1"></i> Nuevo Ejidatario
        </a>
    </div>

    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                    <input
                        type="text"
                        id="searchEjidatario"
                        class="form-control"
                        placeholder="Buscar por nombre, CURP o RFC...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>CURP</th>
                        <th>RFC</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($usuarios as $fila)
                        @php
                            $nombreLimpio = strtolower(trim($fila->Nombres . ' ' . $fila->Apellido_Paterno . ' ' . $fila->Apellido_Materno));
                        @endphp
                        <tr class="fila-ejidatario"
                            data-search="{{ strtolower($nombreLimpio . ' ' . ($fila->CURP ?? '') . ' ' . ($fila->RFC ?? '')) }}">
                            <td>{{ $fila->Nombres }} {{ $fila->Apellido_Paterno }} {{ $fila->Apellido_Materno }}</td>
                            <td>{{ $fila->CURP }}</td>
                            <td>{{ $fila->RFC }}</td>
                            <td>
                                <a href="{{ route('ejidatarios.edit', $fila->Id_Usuario) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <form action="{{ route('ejidatarios.destroy', $fila->Id_Usuario) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Está seguro de eliminar este ejidatario?');">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No se encontraron ejidatarios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- Buscador JS --}}
<script>

    document.getElementById('searchEjidatario').addEventListener('input', function(e) {

        const term = e.target.value.toLowerCase();

        const filas = document.querySelectorAll('.fila-ejidatario');

        filas.forEach(fila => {

            const contenido = fila.getAttribute('data-search');

            fila.classList.toggle('d-none', !contenido.includes(term));

        });

    });

</script>

@endsection