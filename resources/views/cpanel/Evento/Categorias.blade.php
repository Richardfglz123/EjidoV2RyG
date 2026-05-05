@extends('cpanel/plantilla')
@section('title','Categorías de Eventos')
@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-tags me-2"></i> Categorías de Eventos
        </h1>
    </div>

    <a href="{{ route('eventos.create') }}" class="btn btn-outline-secondary shadow-sm">
        <i class="fas fa-arrow-left me-1"></i> Volver a Registro de Evento
    </a>
    <div class="card card-ejidal mb-4 shadow-sm">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-plus-circle me-2"></i> Nueva Categoría
        </div>
        <form action="{{ route('categorias.store') }}" method="POST">
            @csrf
            <div class="card-body p-4">
                <div class="row align-items-end">
                    <div class="col-md-9">
                        <label class="form-label fw-bold text-ejidal">Nombre de la Categoría</label>
                        <input type="text" name="Nombre_Categoria" class="form-control" placeholder="Ej. Asamblea Extraordinaria" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-ejidal w-100 shadow-sm">
                            <i class="fas fa-save me-2"></i> Guardar
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-list-ul me-2"></i> Listado de Categorías
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Nombre de Categoría</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($categorias as $cat)
                        <tr>
                            <td class="ps-4 text-muted">#{{ $cat->Id_Categoria_Evento }}</td>
                            <td class="fw-bold">{{ $cat->Nombre_Categoria }}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-outline-warning btn-sm"
                                            onclick="openEditModal({{ $cat->Id_Categoria_Evento }}, '{{ $cat->Nombre_Categoria }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('categorias.destroy', $cat->Id_Categoria_Evento) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Borrar?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header card-header-ejidal text-white">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar Categoría</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label fw-bold text-ejidal">Nombre de la Categoría</label>
                        <input type="text" name="Nombre_Categoria" id="edit_nombre" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-ejidal">Actualizar Cambios</button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, nombre) {
            document.getElementById('edit_nombre').value = nombre;
            let form = document.getElementById('editForm');
            form.action = `/categorias/${id}`;

            let myModal = new bootstrap.Modal(document.getElementById('editModal'));
            myModal.show();
        }
    </script>

@endsection