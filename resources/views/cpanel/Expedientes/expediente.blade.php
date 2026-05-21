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
                @forelse($data as $fila)
                    @php
                        // Filtramos o buscamos los documentos del usuario
                        $ine = $fila->documentos->where('nombre_documento', 'INE')->first();
                        $curp = $fila->documentos->where('nombre_documento', 'CURP')->first();
                        $dom = $fila->documentos->where('nombre_documento', 'DOMICILIO')->first();
                    @endphp
                    <tr class="fila-ejidatario" data-search="{{ strtolower($fila->Nombres . ' ' . $fila->CURP) }}">
                        <td>{{ $fila->Nombres }} {{ $fila->Apellido_Paterno }}</td>
                        <td>
                            @if($ine) <a href="{{ route('ver.expediente', $ine->ruta_archivo) }}" target="_blank" class="badge bg-success text-decoration-none">INE</a> @else <span class="badge bg-secondary">Sin INE</span> @endif
                            @if($curp) <a href="{{ route('ver.expediente', $curp->ruta_archivo) }}" target="_blank" class="badge bg-success text-decoration-none">CURP</a> @else <span class="badge bg-secondary">Sin CURP</span> @endif
                            @if($dom) <a href="{{ route('ver.expediente', $dom->ruta_archivo) }}" target="_blank" class="badge bg-success text-decoration-none">DOM</a> @else <span class="badge bg-secondary">Sin DOM</span> @endif
                        </td>
                        <td>{{ $fila->CURP }}</td>
                        <td>
                            <a href="{{ route('ejidatarios.edit', $fila->Id_Usuario) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Gestionar Docs
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">No hay registros.</td></tr>
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