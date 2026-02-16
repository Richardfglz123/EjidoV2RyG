<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Egresos</title>
    <style>
        body { font-family: sans-serif; font-size: 9pt; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #00A651; margin-bottom: 20px; }
        .header h1 { color: #00A651; margin: 0; }
        .styled-table { width: 100%; border-collapse: collapse; }
        .styled-table thead { background-color: #00A651; color: white; }
        .styled-table th, .styled-table td { padding: 10px; border: 1px solid #ddd; }
        .text-danger { color: #d9534f; font-weight: bold; }
        .total-box { margin-top: 20px; text-align: right; font-size: 12pt; font-weight: bold; color: #00A651; }
    </style>
</head>
<body>
<div class="header">
    <h1>REPORTE GENERAL DE GASTOS</h1>
    <p>SISTEMA EJIDAL - REGISTRO DE EGRESOS</p>
</div>

<table class="styled-table">
    <thead>
    <tr>
        <th>Fecha</th>
        <th>Responsable</th>
        <th>Concepto</th>
        <th>Monto</th>
    </tr>
    </thead>
    <tbody>
    @php $total = 0; @endphp
    @foreach($gastos as $g)
        <tr>
            <td>{{ \Carbon\Carbon::parse($g->Fecha)->format('d/m/Y') }}</td>
            <td>{{ $g->Responsable }}</td>
            <td>{{ $g->Concepto }}</td>
            <td class="text-danger">$ {{ number_format($g->Monto, 2) }}</td>
        </tr>
        @php $total += $g->Monto; @endphp
    @endforeach
    </tbody>
</table>

<div class="total-box">
    TOTAL DE EGRESOS: $ {{ number_format($total, 2) }}
</div>
</body>
</html>
