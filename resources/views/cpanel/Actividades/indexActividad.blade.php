@extends('cpanel/plantilla')
@section('title','Index')<!--home nombre de la pestaña-->
@section('content')

<!-- TABLA DE REGISTROS -->
<div class="card card-ejidal">
    <div class="card-header card-header-ejidal">
        <i class="fas fa-list me-2"></i> Actividades Registradas
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped align-middle">
            <thead>
            <tr>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Fecha de inicio</th>
                <th>Fecha de fin</th>
                <th>Estado</th>
                <th>Fecha de registro</th>
                <th>Fecha nueva</th>
                <th>Fecha de realización</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $fila)
                <tr>
                    <td>{{$fila->Tipo}}</td>
                    <td>{{$fila->Descripcion}}</td>
                    <td>{{$fila->FechaInicio}}</td>
                    <td>{{$fila->FechaFin}}</td>
                    <td>{{$fila->Estado_Actividad}}</td>
                    <td>{{$fila->Registro_Original}}</td>
                    <td>{{$fila->Nueva_Fecha}}</td>
                    <td>{{$fila->Fecha_Realizo}}</td>
                <td>

                    <a href="{{ route('actividades.edit', $fila->Id_Actividad) }}"
                       class="btn btn-sm btn-outline-success me-1"
                       title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>

                    <form action="{{ route('actividades.destroy', $fila->Id_Actividad) }}"
                          method="POST"
                          style="display:inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-sm btn-outline-danger"
                                title="Eliminar"
                                onclick="return confirm('¿Estás seguro que deseas eliminar esta actividad?')">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
