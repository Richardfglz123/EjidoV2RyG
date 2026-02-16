@extends('Cpanel/plantilla')

@section('title', 'Gráficas del sistema')

@section('content')

    <div class="container mt-4">

        <h2 class="mb-4 text-center">Gráficas del Sistema Ejidal</h2>

        <!-- Ejidatarios -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                Registros de Ejidatarios por Semana
            </div>
            <div class="card-body">
                <canvas id="ejidatariosChart"></canvas>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // === Ejidatarios ===
        let ejidatariosLabels = @json($ejidatario->pluck('semana'));
        let ejidatariosData = @json($ejidatario->pluck('total'));

        new Chart(document.getElementById('ejidatariosChart'), {
            type: 'line',
            data: {
                labels: ejidatariosLabels,
                datasets: [{
                    label: 'Ejidatarios registrados',
                    data: ejidatariosData,
                    borderWidth: 7
                }]
            }
        });
    </script>

@endsection
