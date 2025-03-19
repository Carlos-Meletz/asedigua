@php
use Carbon\Carbon;
@endphp
@include('components.header', ['agencia' => $movimiento->agencia])

<div class="max-w-4xl py-2 mx-auto bg-white rounded-lg shadow-md">
    <!-- Título -->
    <hr>
    <h2 class="text-xl font-bold text-center text-gray-800">TRANSACCIONES VARIOS</h2>
    <hr>

    <!-- Datos Principales -->
    <div class="py-2 space-y-2 text-xs text-gray-700">
        <div class="grid grid-flow-col grid-rows-4 gap-4">
            <div class="col-span-2">
                <p><strong>DESCRIPCION: </strong> {{ $movimiento->descripcion ?? 'N/A' }}</p>
            </div>
            <div class="text-right">
                <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($movimiento->fecha)->format('Y/m/d') }}</p>
                <p><strong>Tipo de Pago:</strong> {{ ucfirst($movimiento->tipo) }}</p>
                <p><strong>Comprobante:</strong> {{ ucfirst($movimiento->comprobante) }}</p>
            </div>
        </div>
    </div>

    <div class="py-1 text-center">

    </div>

    <!-- Tabla de Detalles -->
    <table class="w-full my-6 text-sm border border-gray-300">
        <tbody>
            <tr class="bg-gray-100">
                <th class="text-left border ">Ingreso: </th>
                <td class="text-right border ">Q{{ number_format($movimiento->ingreso, 2) }}</td>
            </tr>
            <tr>
                <th class="text-left border ">Egreso</th>
                <td class="text-right border ">Q{{ number_format($movimiento->egreso, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Firma -->
    <div class="flex justify-center py-6">
        <div class="text-center">
            <p class="text-gray-700">f)_______________________</p>
            <p class="text-gray-700">Firma y Sello de Caja</p>
        </div>
    </div>
    <div class="flex items-center justify-center text-xs ">
        <p>Este recibo no es válido si no esta firmado y sellado.</p>
    </div>
</div>