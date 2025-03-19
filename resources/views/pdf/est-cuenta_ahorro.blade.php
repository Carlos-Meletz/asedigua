@php
use Carbon\Carbon;
@endphp
@include('components.header', ['agencia' => $ahorro->agencia])
<div class="max-w-4xl p-6 m-8 bg-white rounded-lg shadow-lg">
    <h2 class="mb-4 text-2xl font-bold text-gray-800">Estado de Cuenta</h2>

    <div class="flex flex-row bg-green-100 ">
        <!-- Primera Columna -->
        <div class="flex-1">
            <p class="text-left"><strong>Agencia:</strong> {{ $ahorro->agencia->nombre }}</p>
            <p class="text-left"><strong>Cliente:</strong> {{ $ahorro->cliente->nombre_completo }}</p>
            <p class="text-left"><strong>Número de Cuenta:</strong> {{ $ahorro->numero_cuenta }}</p>
            <p class="text-left"><strong>Tipo:</strong> {{ ucfirst($ahorro->tipo) }}</p>
        </div>

        <!-- Segunda Columna -->
        <div class="flex-1">
            <p class="text-left"><strong>Fecha de Apertura:</strong> {{
                Carbon::parse($ahorro->fecha_apertura)->format('d/m/Y') }}
            </p>
            <p class="text-left"><strong>Saldo Actual:</strong> <span class="font-semibold text-green-600">Q{{
                    number_format($ahorro->saldo, 2) }}</span></p>
            <p class="text-left"><strong>Interés Acumulado:</strong> <span class="font-semibold text-blue-600">Q{{
                    number_format($ahorro->interes_acumulado, 2) }}</span></p>
        </div>
    </div>

    <h3 class="mb-3 text-xl font-semibold text-center text-gray-700">Movimientos de la Cuenta</h3>
    <div class="overflow-x-auto">
        <table class="w-full overflow-hidden border border-collapse border-gray-300 rounded-lg">
            <thead class="bg-gray-700">
                <tr>
                    <th class="px-4 py-2">Fecha</th>
                    <th class="px-4 py-2">Cpr.</th>
                    <th class="px-4 py-2">Tipo</th>
                    <th class="px-4 py-2">Depósito</th>
                    <th class="px-4 py-2">Retiro</th>
                    <th class="px-4 py-2">Interés</th>
                    <th class="px-4 py-2">Saldo</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @foreach ($movimientos as $movimiento)
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="px-4 py-2">{{ Carbon::parse($movimiento->fecha)->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">{{ $movimiento->comprobante }}</td>
                    <td class="px-4 py-2 capitalize">{{ $movimiento->tipo }}</td>
                    <td class="px-4 py-2 ">Q{{ number_format($movimiento->deposito, 2) }}
                    </td>
                    <td class="px-4 py-2 ">Q{{ number_format($movimiento->retiro, 2) }}</td>
                    <td class="px-4 py-2 ">Q{{ number_format($movimiento->interes, 2) }}</td>
                    <td class="px-4 py-2 font-semibold">Q{{ number_format($movimiento->saldo, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="mt-4 text-sm text-gray-500">Generado el {{ now()->format('d/m/Y H:i') }}</p>
</div>