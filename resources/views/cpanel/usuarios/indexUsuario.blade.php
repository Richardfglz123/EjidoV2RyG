@extends('cpanel/plantilla')
@section('title','Ejido San Rafael Ixtapalucan')
@section('content')

    <div class="card card-ejidal">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-list me-2"></i> Usuarios Registrados
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                <tr>
                    <th>Nombres</th>
                    <th>Apellido Paterno</th>
                    <th>Apellido Materno</th>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Telefono</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($data as $fila)
                    <tr>
                        <td>{{$fila->Nombres}}</td>
                        <td>{{$fila->Apellido_Paterno}}</td>
                        <td>{{$fila->Apellido_Materno}}</td>
                        <td>{{$fila->Usuario}}</td>
                        <td>{{$fila->Correo}}</td>
                        <td>{{$fila->Telefono}}</td>
                        <td>
                            <a href="{{url('/admon/Usuarios/'.$fila->Id_Usuario.'/edit')}}">
                                Editar
                            </a>
                        </td>
                        <td>
                            <form action="{{url('/admon/Usuarios/'.$fila->Id_Usuario) }}" method="post">
                                @csrf
                                {{ method_field('DELETE') }}
                                <input class="btn btn-outline-primary" type="submit" onclick="return confirm('¿Estas seguro que lo quieres eliminar?')" value="Eliminar">
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <a href="{{ route('reportes.usuarios.pdf') }}" class="btn btn-primary" target="_blank">Generar PDF Usuarios</a>
        <a href="{{ route('reportes.usuarios.excel') }}" class="btn btn-success">Descargar Excel Usuarios</a>

    </div>
@endsection
