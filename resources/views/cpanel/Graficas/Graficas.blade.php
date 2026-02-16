@extends('Cpanel/plantilla')

@section('title', 'Gráficas del sistema')

@section('content')

    <div class="container mt-4">

        <h2 class="mb-4 text-center">Gráficas del Sistema Ejidal</h2>

        <!-- Usuarios -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Registros de Usuarios por Semana
            </div>
            <div class="card-body">
                <canvas id="usuariosChart"></canvas>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // === Usuarios ===
        let usuariosLabels = @json($usuario->pluck('semana'));
        let usuariosData = @json($usuario->pluck('total'));

        new Chart(document.getElementById('usuariosChart'), {
            type: 'line',
            data: {
                labels: usuariosLabels,
                datasets: [{
                    label: 'Usuarios registrados',
                    data: usuariosData,
                    borderWidth: 7,
                    fill: false
                }]
            }
        });
    </script>


@endsection
