@php
use Carbon\Carbon;
@endphp
@include('components.header', ['agencia' => $movimiento->agencia])

<div class="max-w-4xl py-2 mx-auto bg-white rounded-lg shadow-md">
    <!-- Título -->
    <hr>
    @if($movimiento->desembolso == 0)
    <h2 class="text-xl font-bold text-center text-gray-800 ">PAGO DE CRÉDITO</h2>
    @else
    <h2 class="text-xl font-bold text-center text-gray-800 ">DESEMBOLSO DE CRÉDITO</h2>
    @endif
    <hr>
    <!-- Datos Principales -->
    <div class="py-2 space-y-2 text-xs text-gray-700">
        <div class="grid grid-flow-col grid-rows-4 gap-4">
            <div class="col-span-2">
                <p><strong>N° CRÉDITO:</strong> {{ $movimiento->credito->codigo }}</p>
                <h4 class="uppercase"><strong>Cliente:</strong>: {{
                    $movimiento->credito->cliente->nombre_completo }}</h4>
                <h4 class="uppercase"><strong>DPI:</strong>: {{ $movimiento->credito->cliente->dpi }}</h4>
                <h4 class="uppercase"><strong>Dirección:</strong>: {{
                    $movimiento->credito->cliente->direccion }}</h4>
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
        @if($movimiento->desembolso > 0)
        <p class="text-sm font-semibold">Monto Total: <span class="text-green-600">Q{{
                number_format($movimiento->desembolso, 2) }}</span></p>
        <p class="text-sm font-semibold">Descuentos: <span class="text-green-600">Q{{
                number_format($movimiento->descuentos, 2) }}</span></p>
        <p class="text-xl font-semibold border border-gray-300"><strong>Líquido Entregado:</strong> <span
                class="text-green-600">Q{{
                number_format($movimiento->egreso, 2) }}</span></p>
        @else
        PAGO: Q {{ number_format($movimiento->ingreso, 2) }}
        @endif
        </span></p>

    </div>

    @if($movimiento->desembolso == 0)
    <!-- Tabla de Detalles -->
    <table class="w-full my-6 text-sm border border-gray-300">
        <tbody>
            <tr class="bg-gray-100">
                <th class="text-left border ">Capital</th>
                <td class="text-right border ">Q {{ number_format($movimiento->capital, 2) }}</td>
            </tr>
            <tr>
                <th class="text-left border ">Interes</th>
                <td class="text-right border ">Q {{ number_format($movimiento->interes, 2) }}</td>
            </tr>
            @if ($movimiento->descint > 0)
            <tr class="bg-gray-100">
                <th class="text-left border ">Descuento Interes</th>
                <td class="text-right border ">Q '. number_format($movimiento->descint, 2) .'</td>
            </tr>
            @endif
            <tr class="bg-gray-100">
                <th class="text-left border ">Interés Moratorio</th>
                <td class="text-right border ">Q {{ number_format($movimiento->mora, 2) }}</td>
            </tr>
            @if ($movimiento->descmora > 0)
            <tr class="bg-gray-100">
                <th class="text-left border ">Descuento Moratorio</th>
                <td class="text-right border ">Q '. number_format($movimiento->descmora, 2) .'</td>
            </tr>
            @endif
            <tr class="bg-gray-100">
                <th class="text-left border ">Otros</th>
                <td class="text-right border ">Q {{ number_format($movimiento->otros, 2) }}</td>
            </tr>
        </tbody>
    </table>
    <div class="py-2 space-y-1 text-gray-700">
        <p class="text-right"><strong>Saldo Capital:</strong> Q {{ number_format($movimiento->saldocap,2) }}</p>
        <hr class="my-2 border-gray-300">
    </div>
    @endif

    <!-- Notas y Agencia -->

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