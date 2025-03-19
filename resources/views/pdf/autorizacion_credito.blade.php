@php
use Carbon\Carbon;
@endphp
@include('components.header', ['agencia' => $credito->agencia])

<div class="max-w-2xl p-6 mx-auto bg-white rounded-lg shadow-md">
    <h2 class="mb-4 text-xl font-bold text-center">AUTORIZACIÓN DE CRÉDITO</h2>

    <div class="flex justify-around mb-4">
        <label class="flex items-center space-x-2">
            <input type="checkbox" checked class="text-blue-500 form-checkbox">
            <span>INDIVIDUAL</span>
        </label>
    </div>

    <div class="mb-6">
        <h3 class="pb-2 text-lg font-semibold border-b">Datos Generales</h3>
        <p><span class="font-bold">Dirección Actual:</span> {{ $credito->cliente->direccion }}</p>
        <p><span class="font-bold">Municipio:</span> {{ $credito->cliente->municipio }} <span
                class="font-bold">Departamento:</span> {{ $credito->cliente->departamento }}</p>
        <p><span class="font-bold">Nombre Cliente:</span> {{ $credito->cliente->nombre_completo }}</p>
        <p><span class="font-bold">Actividad Económica:</span> MICROEMPRESA</p>
        <p><span class="font-bold">Destino del Crédito:</span> CAPITALIZACIÓN DE NEGOCIO</p>
    </div>

    <div class="mb-6">
        <h3 class="pb-2 text-lg font-semibold border-b">Análisis de las Garantías</h3>
        <p><span class="font-bold">Valor:</span> Q{{ number_format($credito->monto_solicitado, 2) }} * 80% = Q{{
            number_format($credito->monto_solicitado * 0.8, 2) }}</p>
        <p><span class="font-bold">Monto del Crédito:</span> Q{{ number_format($credito->monto_aprobado, 2) }}</p>

        <div class="mb-6">
            <h3 class="pb-2 text-lg font-semibold border-b">Condiciones Generales del Crédito</h3>
            <p><span class="font-bold">Monto a Otorgar:</span> Q{{ number_format($credito->monto_aprobado, 2) }} <span
                    class="font-bold">Plazo:</span> {{ $credito->plazo }} {{ $credito->tipo_plazo }}</p>
            <p><span class="font-bold">Forma de Desembolso:</span> Efectivo</p>
            <p><span class="font-bold">Forma de Pago:</span>
                <label class="inline-flex items-center space-x-2">
                    <input type="checkbox" checked class="text-blue-500 form-checkbox">
                    <span>{{ $credito->tipo_plazo }}</span>
            </p>
            <p><span class="font-bold">Tasa de Interés:</span> {{ $credito->interes_anual }}%</p>
            <p><span class="font-bold">Fuente de Fondos:</span> {{ $credito->fondo->nombre }}</p>
        </div>

        <div class="mb-6">
            <h3 class="pb-2 text-lg font-semibold border-b">Lugar y Fecha</h3>
            <p>Sololá, {{ date('d') }} de {{ strtoupper(date('F')) }} de {{ date('Y') }}</p>
        </div>

        <div class="mt-6 text-center">
            <p class="mb-2">__________________________</p>
            <p>Firma y Sello</p>
        </div>
    </div>