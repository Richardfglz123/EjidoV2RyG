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
                        <input type="text" name="Nombres" class="form-control @error('Nombres') is-invalid @enderror"
                               value="{{ old('Nombres', $fila->Nombres) }}" required>
                        @error('Nombres')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label>Apellido Paterno</label>
                        <input type="text" name="Apellido_Paterno" class="form-control @error('Apellido_Paterno') is-invalid @enderror"
                               value="{{ old('Apellido_Paterno', $fila->Apellido_Paterno) }}" required>
                        @error('Apellido_Paterno')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label>Apellido Materno</label>
                        <input type="text" name="Apellido_Materno" class="form-control @error('Apellido_Materno') is-invalid @enderror"
                               value="{{ old('Apellido_Materno', $fila->Apellido_Materno) }}" required>
                        @error('Apellido_Materno')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Usuario</label>
                        <input type="text" name="Usuario" class="form-control @error('Usuario') is-invalid @enderror"
                               value="{{ old('Usuario', $fila->Usuario) }}" required>
                        @error('Usuario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label>Correo</label>
                        <input type="email" name="Correo" class="form-control @error('Correo') is-invalid @enderror"
                               value="{{ old('Correo', $fila->Correo) }}" required>
                        @error('Correo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label>Teléfono</label>
                        <input type="text" name="Telefono"
                               class="form-control @error('Telefono') is-invalid @enderror"
                               value="{{ old('Telefono', $fila->Telefono) }}"
                               required
                               maxlength="10"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        @error('Telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Exactamente 10 números.</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Nueva contraseña (opcional)</label>
                        <input type="password" name="Contraseña"
                               class="form-control @error('Contraseña') is-invalid @enderror">
                        @error('Contraseña')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Requisito: 8 caracteres, 1 mayúscula y 1 número.</small>
                    </div>

                    <div class="col-md-6">
                        <label>Confirmar contraseña</label>
                        <input type="password" name="Contraseña_confirmation" class="form-control">
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
