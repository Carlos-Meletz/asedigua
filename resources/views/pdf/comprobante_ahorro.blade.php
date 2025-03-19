@php
use Carbon\Carbon;
@endphp
@include('components.header', ['agencia' => $movimiento->agencia])
<div class="max-w-4xl py-2 mx-auto bg-white rounded-lg shadow-md">
    <!-- Título -->
    <hr>
    <h2 class="text-xl font-bold text-center text-gray-800 ">TRANSACCION DE AHORRO</h2>
    <hr>
    <!-- Datos Principales -->

    <div class="py-2 space-y-2 text-xs text-gray-700">
        <div class="grid grid-flow-col grid-rows-4 gap-4">
            <div class="col-span-2">
                <p><strong>N° Cuenta:</strong> {{ $movimiento->ahorro->numero_cuenta }}</p>
                <h4 class="uppercase"><strong>Cliente:</strong>: {{
                    $movimiento->ahorro->cliente->nombre_completo }}</h4>
                <h4 class="uppercase"><strong>DPI:</strong>: {{ $movimiento->ahorro->cliente->dpi }}</h4>
                <h4 class="uppercase"><strong>Dirección:</strong>: {{
                    $movimiento->ahorro->cliente->direccion }}</h4>
            </div>
            <div class="text-right">
                <p class="row-start-4"><strong>Comprobante:</strong><span class="text-xl">{{
                        $movimiento->comprobante
                        }}</span></p>
                <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }}</p>
                <p><strong>Tipo de Pago:</strong> {{ ucfirst($movimiento->tipo) }}</p>
            </div>
        </div>
    </div>
    <div class="py-1 text-center">
        MONTO: Q {{ number_format($movimiento->deposito, 2) }}
    </div>

    <!-- Tabla de Detalles -->
    <table class="w-full my-6 text-sm border border-gray-300">
        <tbody>
            <tr class="bg-gray-100">
                <th class="p-2 text-left border">Depósito</th>
                <td class="p-2 text-right border">Q{{ number_format($movimiento->deposito, 2) }}</td>
            </tr>
            <tr>
                <th class="p-2 text-left border">Retiro</th>
                <td class="p-2 text-right border">Q{{ number_format($movimiento->retiro, 2) }}</td>
            </tr>
            <tr class="bg-gray-100">
                <th class="p-2 text-left border">Interés</th>
                <td class="p-2 text-right border">Q{{ number_format($movimiento->interes, 2) }}</td>
            </tr>
            <tr>
                <th class="p-2 text-left border">Penalización</th>
                <td class="p-2 text-right border">Q{{ number_format($movimiento->penalizacion, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Notas y Agencia -->
    <div class="space-y-1 text-center text-gray-700">
        <p><strong>Notas:</strong> {{ $movimiento->notas ?? 'N/A' }}</p>
    </div>

    <!-- Firma -->
    <div class="grid grid-flow-col grid-rows-4 gap-4 py-6">
        <div class="text-center">
            <p class="text-gray-700">f)_______________________</p>
            <p class="text-gray-700">Cliente</p>
        </div>
        <div class="text-center">
            <p class="text-gray-700">f)_______________________</p>
            <p class="text-gray-700">Firma y Sello de Caja</p>
        </div>
    </div>
    <div class="flex items-center justify-center text-xs ">
        <p>Este recibo no es válido si no esta firmado y sellado.</p>
    </div>
</div>