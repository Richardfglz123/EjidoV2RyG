@extends('cpanel/plantilla')
@section('title','Editar usuario')

@section('content')

    <div class="card card-ejidal">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-user-edit me-2"></i> Editar usuario
        </div>

        <form action="{{ url('/admon/Usuarios/'.$fila->Id_Usuario) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Nombre(s)</label>
                        <input type="text" name="Nombres" class="form-control"
                               value="{{ $fila->Nombres }}" required>
                    </div>
                    <div class="col-md-4">
                        <label>Apellido Paterno</label>
                        <input type="text" name="Apellido_Paterno" class="form-control"
                               value="{{ $fila->Apellido_Paterno }}" required>
                    </div>
                    <div class="col-md-4">
                        <label>Apellido Materno</label>
                        <input type="text" name="Apellido_Materno" class="form-control"
                               value="{{ $fila->Apellido_Materno }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Usuario</label>
                        <input type="text" name="Usuario" class="form-control"
                               value="{{ $fila->Usuario }}" required>
                    </div>
                    <div class="col-md-4">
                        <label>Correo</label>
                        <input type="email" name="Correo" class="form-control"
                               value="{{ $fila->Correo }}" required>
                    </div>
                    <div class="col-md-4">
                        <label>Teléfono</label>
                        <input type="text" name="Telefono" class="form-control"
                               value="{{ $fila->Telefono }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Nueva contraseña (opcional)</label>
                        <input type="password" name="Contraseña" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Confirmar contraseña</label>
                        <input type="password" name="Contraseña_confirmation" class="form-control">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label>Tipo de usuario</label>
                        <select name="Id_Rol" class="form-select" required>
                            <option value="">-- Seleccione un rol --</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->Id_Rol }}"
                                    {{ $rol->Id_Rol == $fila->Id_Rol ? 'selected' : '' }}>
                                    {{ $rol->Tipo_Rol }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="text-end">
                    <a href="{{ route('Usuarios.index') }}" class="btn btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-ejidal">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>

            </div>
        </form>
    </div>

@endsection
