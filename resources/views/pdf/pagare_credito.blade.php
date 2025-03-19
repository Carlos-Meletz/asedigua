@php
use App\Models\Locacion;
$departamento = Locacion::where('id', $credito->cliente->departamento)->value('name');
$municipio = Locacion::where('id', $credito->cliente->municipio)->value('name');
@endphp
<div class="max-w-3xl p-6 mx-auto bg-white rounded-lg shadow-md">
    <h2 class="mb-6 text-2xl font-bold text-center">PAGARÉ DE CRÉDITO</h2>

    <p class="mb-4">Por la cantidad de <b>Q{{ number_format($credito->monto_desembolsado, 2) }}</b>, en la ciudad de
        <b>{{ $municipio }}</b>, departamento de <b>{{ $departamento }}</b>, el día
        <b>{{ date('d') }}</b> de <b>{{ strtoupper(date('F')) }}</b> de <b>{{ date('Y') }}</b>, yo, <b>{{
            $credito->cliente->nombre_completo }}</b>, mayor de edad, con DPI <b>{{ $credito->cliente->dpi }}</b>,
        domiciliado en <b>{{ $credito->cliente->direccion }}</b>, me obligo a pagar incondicionalmente a la orden de
        <b>ASPEGUA</b>, la cantidad mencionada.
    </p>

    <div class="mb-6">
        <h3 class="pb-2 text-lg font-bold border-b">CONDICIONES DEL PAGO</h3>
        <p>Este pagaré deberá ser pagado en <b>{{ $credito->plazo }} cuotas</b>, mediante cuotas <b>{{
                $credito->tipo_cuota }}</b>, conforme al plan de pagos proporcionado.</p>
    </div>

    <div class="mb-6">
        <h3 class="pb-2 text-lg font-bold border-b">TASA DE INTERÉS</h3>
        <p>La tasa de interés aplicable será del <b>{{ $credito->interes_anual }}% anual</b>.</p>
    </div>

    <div class="mb-6">
        <h3 class="pb-2 text-lg font-bold border-b">INCUMPLIMIENTO</h3>
        <p>En caso de mora, se aplicará un interés moratorio del <b>{{ $credito->crelinea->tasa_mora }}% mensual</b> y
            se podrá ejecutar la garantía proporcionada.</p>
    </div>

    <div class="flex justify-between mt-6">
        <div class="text-center">
            <p class="mb-2">__________________________</p>
            <p class="font-bold">EL DEUDOR</p>
            <p>{{ $credito->cliente->nombre_completo }}</p>
        </div>

        <div class="text-center">
            <p class="mb-2">__________________________</p>
            <p class="font-bold">EL ACREEDOR</p>
            <p>Org PRESTAMOS</p>
        </div>
    </div>
</div>