@extends('cpanel/plantilla')
@section('title','Registro usuario')
@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-users me-2"></i> Usuarios
        </h1>
    </div>

    <div class="card card-ejidal">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-user-plus me-2"></i> Nuevo usuario
        </div>

        <form action="{{ route('Usuarios.store') }}" method="POST">
            @csrf

            <div class="card-body">

                {{-- Nombres y Apellidos --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Nombre(s)</label>
                        <input type="text" name="Nombres"
                               class="form-control @error('Nombres') is-invalid @enderror"
                               value="{{ old('Nombres') }}" required>
                        @error('Nombres')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label>Apellido Paterno</label>
                        <input type="text" name="Apellido_Paterno"
                               class="form-control @error('Apellido_Paterno') is-invalid @enderror"
                               value="{{ old('Apellido_Paterno') }}" required>
                        @error('Apellido_Paterno')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label>Apellido Materno</label>
                        <input type="text" name="Apellido_Materno"
                               class="form-control @error('Apellido_Materno') is-invalid @enderror"
                               value="{{ old('Apellido_Materno') }}" required>
                        @error('Apellido_Materno')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Usuario, correo, teléfono --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Usuario</label>
                        <input type="text" name="Usuario"
                               class="form-control @error('Usuario') is-invalid @enderror"
                               value="{{ old('Usuario') }}" required>
                        @error('Usuario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label>Correo</label>
                        <input type="email" name="Correo"
                               class="form-control @error('Correo') is-invalid @enderror"
                               value="{{ old('Correo') }}" required>
                        @error('Correo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label>Teléfono</label>
                        <input type="text" name="Telefono"
                               class="form-control @error('Telefono') is-invalid @enderror"
                               value="{{ old('Telefono') }}" required>
                        @error('Telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Contraseña --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Contraseña</label>
                        <input type="password" name="Contraseña"
                               class="form-control @error('Contraseña') is-invalid @enderror"
                               required minlength="6">
                        @error('Contraseña')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label>Confirmar contraseña</label>
                        <input type="password" name="Contraseña_confirmation"
                               class="form-control" required minlength="6">
                    </div>
                </div>

                {{-- ROL (FIX REAL AQUÍ) --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label>Rol</label>
                        <select name="Id_Rol"
                                class="form-select @error('Id_Rol') is-invalid @enderror"
                                required>
                            <option value="">Seleccione un rol</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->Id_Rol }}"
                                    {{ old('Id_Rol') == $rol->Id_Rol ? 'selected' : '' }}>
                                    {{ $rol->Tipo_Rol }}
                                </option>
                            @endforeach
                        </select>
                        @error('Id_Rol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Botones --}}
                <div class="text-end">
                    <a href="{{ route('Usuarios.index') }}" class="btn btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-ejidal">
                        <i class="fas fa-save me-1"></i> Guardar Usuario
                    </button>
                </div>

            </div>
        </form>
    </div>

@endsection
