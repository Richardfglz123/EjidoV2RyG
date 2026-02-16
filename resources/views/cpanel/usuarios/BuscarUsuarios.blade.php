@extends('cpanel/plantilla')
@section('title','Buscar Usuario')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-search me-2"></i> Buscar Usuario
        </h1>
    </div>

    <div class="card card-ejidal mb-4">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> Búsqueda
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url('/admon/Usuarios/buscar') }}" class="row g-2">
                <div class="col-md-5">
                    <input
                        type="text"
                        name="nombre"
                        class="form-control"
                        placeholder="Buscar por nombre..."
                        value="{{ request('nombre') }}"
                    >
                </div>

                <div class="col-md-5">
                    <input
                        type="text"
                        name="apellido"
                        class="form-control"
                        placeholder="Buscar por apellido..."
                        value="{{ request('apellido') }}"
                    >
                </div>

                <div class="col-md-2">
                    <button class="btn btn-ejidal w-100">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(request()->filled('nombre') || request()->filled('apellido'))
        <div class="card card-ejidal">
            <div class="card-header card-header-ejidal">
                <i class="fas fa-list me-2"></i> Resultados
            </div>

            <div class="card-body table-responsive">

                <div class="mb-2 text-muted">
                    Resultados encontrados: {{ $usuarios->total() }}
                </div>

                <table class="table table-striped align-middle">
                    <thead>
                    <tr>
                        <th>Nombres</th>
                        <th>Apellido Paterno</th>
                        <th>Apellido Materno</th>
                        <th>Usuario</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($usuarios as $u)
                        <tr>
                            <td>{{ $u->Nombres }}</td>
                            <td>{{ $u->Apellido_Paterno }}</td>
                            <td>{{ $u->Apellido_Materno }}</td>
                            <td>{{ $u->Usuario }}</td>
                            <td class="text-center">
                                <a href="{{ url('/admon/Usuarios/'.$u->Id_Usuario.'/edit') }}"
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No se encontraron usuarios
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-center small">
                    {{ $usuarios->links('pagination::bootstrap-4') }}
                </div>

            </div>
        </div>
    @endif

@endsection
