@php
use App\Models\Locacion;
$departamento = Locacion::where('id', $credito->cliente->departamento)->value('name');
$municipio = Locacion::where('id', $credito->cliente->municipio)->value('name');
@endphp

<div class="max-w-3xl p-6 mx-auto bg-white rounded-lg shadow-md">
    <h2 class="mb-6 text-2xl font-bold text-center">CONTRATO DE CRÉDITO</h2>

    <p class="mb-4">En la ciudad de <b>{{ $municipio }}</b>, departamento de <b>{{
            $departamento }}</b>, el día <b>{{ date('d') }}</b> de <b>{{ strtoupper(date('F')) }}</b>
        de <b>{{ date('Y') }}</b>, entre:</p>

    <p class="mb-4 font-semibold">**ORGANIZACIÓN DE PRÉSTAMOS S.A.**, con domicilio en {{ $credito->agencia->direccion
        }}, representada en este acto por su representante legal, en adelante denominado **"EL ACREEDOR"**, y</p>

    <p class="mb-4 font-semibold">**{{ $credito->cliente->nombre_completo }}**, mayor de edad, con DPI **{{
        $credito->cliente->dpi }}**, domiciliado en **{{ $credito->cliente->direccion }}**, en adelante denominado **"EL
        DEUDOR"**, convienen lo siguiente:</p>

    <div class="mb-6">
        <h3 class="pb-2 text-lg font-bold border-b">PRIMERO: MONTO DEL PRÉSTAMO</h3>
        <p>EL ACREEDOR otorga a EL DEUDOR un crédito por la cantidad de **Q{{
            number_format($credito->monto_desembolsado, 2) }}**, a ser pagado bajo las condiciones establecidas en este
            contrato.</p>
    </div>

    <div class="mb-6">
        <h3 class="pb-2 text-lg font-bold border-b">SEGUNDO: PLAZO Y FORMA DE PAGO</h3>
        <p>El crédito deberá ser pagado en un plazo de **{{ $credito->plazo }} meses**, con pagos {{
            $credito->tipo_plazo }}, de acuerdo con el plan de pagos proporcionado por EL ACREEDOR.</p>
    </div>

    <div class="mb-6">
        <h3 class="pb-2 text-lg font-bold border-b">TERCERO: TASA DE INTERÉS</h3>
        <p>La tasa de interés anual será del **{{ $credito->interes_anual }}%**, aplicable sobre saldos.</p>
    </div>

    <div class="mb-6">
        <h3 class="pb-2 text-lg font-bold border-b">CUARTO: GARANTÍAS</h3>
        <p>Para garantizar el cumplimiento del pago, EL DEUDOR deja como garantía el siguiente bien:</p>
        <p class="italic text-gray-600">Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestias et ipsam,
            minima quibusdam eligendi ipsum distinctio nam sit impedit laudantium tempore, laboriosam accusantium quasi
            obcaecati dolor corrupti quaerat, aliquam magnam.</p>
    </div>

    <div class="mb-6">
        <h3 class="pb-2 text-lg font-bold border-b">QUINTO: INCUMPLIMIENTO</h3>
        <p>En caso de mora o incumplimiento, EL ACREEDOR tendrá derecho a ejecutar la garantía o tomar las acciones
            legales correspondientes.</p>
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
            <p>ORG. PRESTAMOS S.A.</p>
        </div>
    </div>
</div>