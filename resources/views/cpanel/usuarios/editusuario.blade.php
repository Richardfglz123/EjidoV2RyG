@extends('cpanel/plantilla')
@section('title','Editar usuario')

@section('content')

    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-user-edit me-2"></i> Editar usuario
        </div>

        <form action="{{ url('/admon/Usuarios/'.$fila->Id_Usuario) }}" method="POST">
            @csrf
            @method('PUT')

            @php
                // Definimos la lógica de permisos una sola vez para reutilizarla
                $rolNombre = session('usuario.rol_nombre') ?? session('usuario.rol');
                $esAdmin = ($rolNombre === 'Administrador');
            @endphp

            <div class="card-body">
                {{-- Fila de Nombres --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Nombre(s)</label>
                        <input type="text" name="Nombres" class="form-control @error('Nombres') is-invalid @enderror" value="{{ old('Nombres', $fila->Nombres) }}" required>
                        @error('Nombres')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label>Apellido Paterno</label>
                        <input type="text" name="Apellido_Paterno" class="form-control @error('Apellido_Paterno') is-invalid @enderror" value="{{ old('Apellido_Paterno', $fila->Apellido_Paterno) }}" required>
                        @error('Apellido_Paterno')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label>Apellido Materno</label>
                        <input type="text" name="Apellido_Materno" class="form-control @error('Apellido_Materno') is-invalid @enderror" value="{{ old('Apellido_Materno', $fila->Apellido_Materno) }}" required>
                        @error('Apellido_Materno')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row mb-3">
                    {{-- Campo Usuario: Ahora condicionado al Administrador --}}
                    <div class="col-md-4">
                        <label>Usuario</label>
                        <input type="text"
                               name="Usuario"
                               class="form-control @error('Usuario') is-invalid @enderror"
                               value="{{ old('Usuario', $fila->Usuario) }}"
                                {{ !$esAdmin ? 'readonly' : '' }}>

                        @error('Usuario')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        @if(!$esAdmin)
                            <small class="text-muted"><i class="fas fa-lock me-1"></i> El nombre de usuario está bloqueado.</small>
                        @else
                            <small class="text-success"><i class="fas fa-unlock me-1"></i> Puedes modificar el nombre de usuario.</small>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label>Correo electrónico</label>
                        <input type="email"
                               name="Correo"
                               class="form-control @error('Correo') is-invalid @enderror"
                               value="{{ old('Correo', $fila->Correo) }}"
                                {{ !$esAdmin ? 'readonly' : '' }}>

                        @error('Correo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        @if(!$esAdmin)
                            <small class="text-muted"><i class="fas fa-lock me-1"></i> Solo el administrador puede modificar el correo.</small>
                        @else
                            <small class="text-success"><i class="fas fa-unlock me-1"></i> Tienes permiso para editar el correo.</small>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label>Teléfono</label>
                        <input type="text" name="Telefono" class="form-control @error('Telefono') is-invalid @enderror" value="{{ old('Telefono', $fila->Telefono) }}" maxlength="10" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        @error('Telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Sección de Contraseñas con botón de ver/ocultar --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Nueva contraseña (opcional)</label>
                        <div class="input-group">
                            <input type="password" name="Contraseña" id="pass1" class="form-control @error('Contraseña') is-invalid @enderror">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('pass1', 'icon1')">
                                <i class="fas fa-eye" id="icon1"></i>
                            </button>
                        </div>
                        @error('Contraseña')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="text-muted">Mínimo 8 caracteres, 1 mayúscula y 1 número</small>
                    </div>

                    <div class="col-md-6">
                        <label>Confirmar contraseña</label>
                        <div class="input-group">
                            <input type="password" name="Contraseña_confirmation" id="pass2" class="form-control">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('pass2', 'icon2')">
                                <i class="fas fa-eye" id="icon2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <a href="{{ route('Usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-ejidal">
                        <i class="fas fa-save me-1"></i> Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

@endsection