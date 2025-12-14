@extends('cpanel/plantilla')
@section('title','Registro usuario')
@section('content')
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <form action="{{ route('Ejidatarios.store') }}" method="POST" class="cmxform" id="signupForm">
                @csrf
            <div class="mb-3">
                <label>Número de Ejidatario</label>
                <input type="number" name="Num_Ejidatario" class="form-control"
                       value="{{ $fila->Num_Ejidatario ?? '' }}">
            </div>

            <div class="mb-3">
                <label>Dirección</label>
                <input type="text" name="Direccion" class="form-control"
                       value="{{ $fila->Direccion ?? '' }}">
            </div>

            <div class="mb-3">
                <label>No. Parcela</label>
                <input type="text" name="No_Parcela" class="form-control"
                       value="{{ $fila->No_Parcela ?? '' }}">
            </div>

            <button class="btn btn-primary">
                {{ isset($fila) ? 'Actualizar' : 'Guardar' }}
            </button>

        </form>

    </main>
@endsection
