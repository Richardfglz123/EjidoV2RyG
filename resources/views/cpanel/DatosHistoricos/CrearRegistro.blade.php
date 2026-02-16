@extends('cpanel/plantilla')
@section('title','Registrar Dato Histórico')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-history me-2"></i> Datos Históricos
        </h1>
    </div>

    <div class="card card-ejidal">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-plus-circle me-2"></i> Nuevo registro histórico
        </div>

        <form method="POST" action="{{ route('datos_historicos.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                @include('cpanel.DatosHistoricos.formRegistro')

                <div class="text-end mt-4">
                    <a href="{{ route('datos_historicos.index') }}" class="btn btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-ejidal">
                        <i class="fas fa-save me-1"></i> Guardar Registro
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
