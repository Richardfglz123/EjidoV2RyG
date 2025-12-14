@extends('cpanel/plantilla')
@section('title', 'Listado de Ejidatarios')

@section('content')
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

        <h2>Listado Completo de Ejidatarios</h2>

        <a href="{{ url('admon/Ejidatarios/create') }}" class="btn btn-success mb-3">
            Nuevo Ejidatario
        </a>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>Número</th>
                <th>Usuario</th>
                <th>Dirección</th>
                <th>Parcela</th>
                <th>Acciones</th>
            </tr>
            </thead>

            <tbody>
            @foreach($data as $fila)
                <tr>
                    <td>{{ $fila->Id_Ejidatario }}</td>
                    <td>{{ $fila->Num_Ejidatario }}</td>
                    <td>{{ $fila->Usuario }}</td>
                    <td>{{ $fila->Direccion }}</td>
                    <td>{{ $fila->No_Parcela }}</td>
                    <td>
                        <a href="{{ url('admon/Ejidatarios/'.$fila->Id_Ejidatario.'/edit') }}"
                           class="btn btn-warning btn-sm">Editar</a>

                        <form action="{{ url('admon/Ejidatarios/'.$fila->Id_Ejidatario) }}"
                              method="POST" style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm"
                                    onclick="return confirm('¿Eliminar este ejidatario?')">
                                Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </main>
@endsection
