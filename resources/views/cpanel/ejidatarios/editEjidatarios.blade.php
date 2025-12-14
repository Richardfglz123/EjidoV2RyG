@extends('cpanel/plantilla')
@section('title','Editar Ejidatario')

@section('content')
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

        <form action="{{ url('admon/Ejidatarios/'.$fila->Id_Ejidatario) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Usuario</label>
                <select name="Id_Usuario" class="form-select" required>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->Id_Usuario }}"
                            {{ $u->Id_Usuario == $fila->Id_Usuario ? 'selected' : '' }}>
                            {{ $u->Nombres }} {{ $u->Apellido_Paterno }} {{ $u->Apellido_Materno }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Número de Ejidatario</label>
                <input type="number" name="Num_Ejidatario"
                       class="form-control"
                       value="{{ $fila->Num_Ejidatario }}" required>
            </div>

            <div class="mb-3">
                <label>Dirección</label>
                <input type="text" name="Direccion"
                       class="form-control"
                       value="{{ $fila->Direccion }}" required>
            </div>

            <div class="mb-3">
                <label>No. Parcela</label>
                <input type="text" name="No_Parcela"
                       class="form-control"
                       value="{{ $fila->No_Parcela }}" required>
            </div>

            <button class="btn btn-primary">Actualizar</button>

        </form>
    </main>
@endsection
