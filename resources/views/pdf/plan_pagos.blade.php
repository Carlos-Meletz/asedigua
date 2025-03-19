@php
use Carbon\Carbon;
@endphp
@include('components.header', ['agencia' => $credito->agencia])

<div class="max-w-4xl p-6 mx-auto bg-white rounded-lg shadow-lg">
    <hr>
    <h2 class="my-2 text-2xl font-semibold text-center text-gray-800">Plan de Pagos</h2>
    <hr>
    <div class="grid grid-cols-2 mt-2 text-xs text-gray-700">
        <p><strong>Número de Crédito:</strong> {{ $credito->codigo }}</p>
        <p><strong>Cliente:</strong> {{ $credito->cliente->nombre_completo }}</p>
        <p><strong>Monto:</strong> Q{{ number_format($credito->monto_solicitado, 2) }}</p>
        <p><strong>Interés Anual:</strong> {{ $credito->interes_anual }}%</p>
        <p><strong>Plazo:</strong> {{ $credito->plazo }} meses</p>
        <p><strong>Fecha de Desembolso:</strong> {{ Carbon::parse($credito->fecha_desembolso)->format('d/m/Y') }}</p>
    </div>

    <div class="mt-6 overflow-x-auto">
        <table class="w-full border border-collapse border-gray-300">
            <thead>
                <tr class="text-xs text-gray-700 bg-gray-200">
                    <th class="px-2 py-1 border border-gray-300">No. Cuota</th>
                    <th class="px-2 py-1 border border-gray-300">Fecha</th>
                    <th class="px-2 py-1 border border-gray-300">Cuota</th>
                    <th class="px-2 py-1 border border-gray-300">Interés</th>
                    <th class="px-2 py-1 border border-gray-300">Capital</th>
                    <th class="px-2 py-1 border border-gray-300">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plan as $pago)
                <tr class="text-xs text-center odd:bg-white even:bg-gray-100">
                    <td class="px-2 py-1 border border-gray-300">{{ $pago['nocuota'] }}</td>
                    <td class="px-2 py-1 border border-gray-300">{{ Carbon::parse($pago['fecha'])->format('d/m/Y') }}
                    </td>
                    <td class="px-2 py-1 border border-gray-300">Q {{ number_format($pago['cuota'], 2) }}</td>
                    <td class="px-2 py-1 border border-gray-300">Q {{ number_format($pago['interes'], 2) }}</td>
                    <td class="px-2 py-1 border border-gray-300">Q {{ number_format($pago['capital'], 2) }}</td>
                    <td class="px-2 py-1 border border-gray-300">Q {{ number_format($pago['saldo'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                @php
                $totalCuota = array_sum(array_column($plan, 'cuota'));
                $totalInteres = array_sum(array_column($plan, 'interes'));
                $totalCapital = array_sum(array_column($plan, 'capital'));
                @endphp
                <tr class="text-xs font-bold text-center bg-gray-100">
                    <td colspan="2" class="px-2 py-1 border border-gray-400">Totales</td>
                    <td class="px-2 py-1 border border-gray-400">Q {{ number_format($totalCuota, 2) }}</td>
                    <td class="px-2 py-1 border border-gray-400">Q {{ number_format($totalInteres, 2) }}</td>
                    <td class="px-2 py-1 border border-gray-400">Q {{ number_format($totalCapital, 2) }}</td>
                    <td class="px-2 py-1 border border-gray-400"></td> <!-- Columna de saldo vacía -->
                </tr>
            </tfoot>
        </table>
    </div>
</div>