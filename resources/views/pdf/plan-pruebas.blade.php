@php
use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Plan de Pagos</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 10px;
        }

        h2 {
            text-align: center;
            font-size: 15px;
            margin: 6px 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            font-size: 12px;
            margin-top: 10px;
        }

        .info-grid p {
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        thead {
            background-color: #e0e0e0;
        }

        th,
        td {
            border: 1px solid #aaa;
            padding: 3px;
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tfoot {
            background-color: #eee;
            font-weight: bold;
        }

        hr {
            margin: 10px 0;
            border: none;
            border-top: 1px solid #ccc;
        }
    </style>
</head>

<body>

    <hr>
    <h2>Plan de Pagos</h2>
    <hr>

    <div class="info-grid">
        <p><strong>Monto:</strong> Q{{ number_format($monto, 2) }}</p>
        <p><strong>Interés Anual:</strong> {{ $tasa }}%</p>
        <p><strong>Plazo:</strong> {{ $plazo }} meses</p>
        {{-- <p><strong>Fecha de Desembolso:</strong> {{ Carbon::parse($fecha_desembolso)->format('d/m/Y') }}</p> --}}
    </div>

    <table>
        <thead>
            <tr>
                <th>No. Cuota</th>
                <th>Fecha</th>
                <th>Cuota</th>
                <th>Capital</th>
                <th>Interés</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($planPagos as $pago)
            <tr>
                <td>{{ $pago['nro_cuota'] }}</td>
                <td>{{ Carbon::parse($pago['fecha'])->format('d/m/Y') }}</td>
                <td>Q {{ number_format($pago['cuota'], 2) }}</td>
                <td>Q {{ number_format($pago['capital'], 2) }}</td>
                <td>Q {{ number_format($pago['interes'], 2) }}</td>
                <td>Q {{ number_format($pago['saldo'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php
            $totalCuota = array_sum(array_column($planPagos, 'cuota'));
            $totalInteres = array_sum(array_column($planPagos, 'interes'));
            $totalCapital = array_sum(array_column($planPagos, 'capital'));
            @endphp
            <tr>
                <td colspan="2">Totales</td>
                <td>Q {{ number_format($totalCuota, 2) }}</td>
                <td>Q {{ number_format($totalCapital, 2) }}</td>
                <td>Q {{ number_format($totalInteres, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

</body>

</html>