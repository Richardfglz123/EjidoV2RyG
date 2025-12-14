@extends('cpanel/plantilla')
@section('title','Registrar Ejidatario')

@section('content')
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

        <h2>Registrar Ejidatario</h2>

        {{-- BUSCADOR DE USUARIOS --}}
        <form method="GET" action="{{ route('Ejidatarios.create') }}" class="mb-4">
            <label>Buscar Usuario</label>
            <div class="input-group">
                <input type="text"
                       name="buscar_usuario"
                       class="form-control"
                       placeholder="Nombre del usuario"
                       value="{{ request('buscar_usuario') }}">

                <button class="btn btn-secondary">
                    Buscar
                </button>
            </div>
        </form>

        {{-- FORMULARIO PRINCIPAL --}}
        <form action="{{ route('Ejidatarios.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label>Usuario</label>
                <select name="Id_Usuario" class="form-control" required>
                    <option value="">-- Seleccione un usuario --</option>

                    @foreach($usuarios as $u)
                        <option value="{{ $u->Id_Usuario }}">
                            {{ $u->Nombres }} {{ $u->Apellido_Paterno }} {{ $u->Apellido_Materno }}
                        </option>
                    @endforeach
                </select>

                @if(empty($usuarios))
                    <small class="text-muted">
                        Escriba un nombre y presione buscar
                    </small>
                @endif
            </div>

            <div class="mb-3">
                <label>Número de Ejidatario</label>
                <input type="number" name="Num_Ejidatario" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Dirección</label>
                <input type="text" name="Direccion" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>No. Parcela</label>
                <input type="text" name="No_Parcela" class="form-control" required>
            </div>

            <button class="btn btn-primary">
                Guardar Ejidatario
            </button>
        </form>

    </main>
@endsection
