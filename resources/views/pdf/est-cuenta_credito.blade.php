@php
use Carbon\Carbon;
$totalPago = 0;
$totalCapital = 0;
$totalInteres = 0;
$totalMora = 0;
$totalOtros = 0;
@endphp
@include('components.header', ['agencia' => $credito->agencia])
<div class="max-w-5xl p-6 mx-auto bg-white rounded-lg shadow-lg">
    <hr>
    <h2 class="py-2 text-xl font-semibold text-center text-gray-800">Estado de Cuenta - Crédito</h2>
    <hr>
    <div class="grid grid-flow-col grid-rows-4 gap-4">
        <div class="col-span-2">
            <p class="font-medium text-gray-700">Cliente: <span class="font-normal">{{ $credito->cliente->nombre
                    }}</span></p>
            <p class="font-medium text-gray-700">Número de Crédito: <span class="font-normal">{{
                    $credito->codigo }}</span></p>
            <p class="font-medium text-gray-700">Monto Aprobado: <span class="font-bold">Q{{
                    number_format($credito->monto_aprobado, 2) }}</span></p>
            <p class="font-medium text-gray-700">Monto Desembolsado: <span class="font-bold">Q{{
                    number_format($credito->monto_desembolsado, 2) }}</span></p>
            <p class="font-medium text-gray-700">Fecha de Desembolso: <span class="font-normal">{{
                    Carbon::parse($credito->fecha_desembolso)->format('d/m/Y') }}</span></p>
            <p class="font-medium text-gray-700">Saldo Pendiente: <span class="font-bold text-red-600">Q{{
                    number_format($credito->saldo_capital, 2) }}</span></p>
        </div>
        {{-- <div class="col-span-2">
            <p class="font-medium text-gray-700">Cliente: <span class="font-normal">{{ $credito->cliente->nombre
                    }}</span></p>
            <p class="font-medium text-gray-700">Número de Crédito: <span class="font-normal">{{
                    $credito->codigo }}</span></p>
            <p class="font-medium text-gray-700">Monto Aprobado: <span class="font-bold">Q{{
                    number_format($credito->monto_aprobado, 2) }}</span></p>
            <p class="font-medium text-gray-700">Monto Desembolsado: <span class="font-bold">Q{{
                    number_format($credito->monto_desembolsado, 2) }}</span></p>
            <p class="font-medium text-gray-700">Fecha de Desembolso: <span class="font-normal">{{
                    Carbon::parse($credito->fecha_desembolso)->format('d/m/Y') }}</span></p>
            <p class="font-medium text-gray-700">Saldo Pendiente: <span class="font-bold text-red-600">Q{{
                    number_format($credito->saldo_capital, 2) }}</span></p>
        </div> --}}

    </div>

    <h3 class="py-2 text-xl font-semibold text-center text-gray-800">Histórico de Abonos</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left text-gray-700 bg-white border-collapse">
            <thead class="text-left bg-gray-200">
                <tr>
                    <th class="p-1 border-b">F. Contable</th>
                    <th class="p-1 border-b">F. Valor</th>
                    <th class="p-1 border-b">CPR</th>
                    <th class="p-1 border-b">Atr</th>
                    <th class="p-1 border-b">Pago</th>
                    <th class="p-1 border-b">Capital</th>
                    <th class="p-1 border-b">Interés</th>
                    <th class="p-1 border-b">Mora</th>
                    <th class="p-1 border-b">Otros</th>
                    <th class="p-1 border-b">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($movimientos as $movimiento)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-1 text-left">{{ Carbon::parse($movimiento->created_at)->format('d/m/Y') }}</td>
                    <td class="p-1 text-left">{{ Carbon::parse($movimiento->fecha)->format('d/m/Y') }}</td>
                    <td class="p-1 text-left">{{ $movimiento->comprobante }}</td>
                    <td class="p-1 text-left">{{ $movimiento->atraso }}</td>
                    <td class="p-1 text-left">Q {{ number_format($movimiento->pago, 2) }}</td>
                    <td class="p-1 text-left">Q {{ number_format($movimiento->capital, 2) }}</td>
                    <td class="p-1 text-left">Q {{ number_format($movimiento->interes, 2) }}</td>
                    <td class="p-1 text-left">Q {{ number_format($movimiento->mora, 2) }}</td>
                    <td class="p-1 text-left">Q {{ number_format($movimiento->otros, 2) }}</td>
                    <td class="p-1 text-left">Q {{ number_format($movimiento->saldocap, 2) }}</td>
                </tr>
                @php
                $totalPago = $totalPago + $movimiento->pago;
                $totalCapital = $totalCapital + $movimiento->capital;
                $totalInteres = $totalInteres + $movimiento->interes;
                $totalMora = $totalMora + $movimiento->mora;
                $totalOtros = $totalOtros + $movimiento->otros;
                @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr class="text-xs font-bold text-left bg-gray-100">
                    <td colspan="4" class="px-2 py-1 border border-gray-400">Totales</td>
                    <td class="px-2 py-1 border border-gray-400">Q {{ number_format($totalPago, 2) }}</td>
                    <td class="px-2 py-1 border border-gray-400">Q {{ number_format($totalCapital, 2) }}</td>
                    <td class="px-2 py-1 border border-gray-400">Q {{ number_format($totalInteres, 2) }}</td>
                    <td class="px-2 py-1 border border-gray-400">Q {{ number_format($totalMora, 2) }}</td>
                    <td class="px-2 py-1 border border-gray-400">Q {{ number_format($totalOtros, 2) }}</td>
                    <td class="px-2 py-1 border border-gray-400"></td> <!-- Columna de saldo vacía -->
                </tr>
            </tfoot>
        </table>
    </div>
</div>