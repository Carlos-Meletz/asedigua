<div class="max-w-4xl p-6 m-8 bg-white rounded-lg shadow-lg">

    <div class="max-w-3xl p-48 mx-auto">
        <h1 class="text-2xl font-bold text-center">CONTRATO DE CUENTA DE AHORRO</h1>
        <h2 class="mt-2 text-lg font-semibold text-center">No. {{ $contrato->numero_cuenta }}</h2>

        <div class="mt-6">
            <p><strong>Entre:</strong> <span class="font-semibold">ORGANIZACION DE PRESTAMOS S.A.</span>, en adelante
                "LA
                ENTIDAD", y</p>
            <p><strong>El Cliente:</strong> <span class="font-semibold">{{ $contrato->cliente->nombre_completo
                    }}</span>, identificado con
                <span class="font-semibold">{{ $contrato->cliente->dpi }}</span>, en adelante "EL AHORRANTE".
            </p>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-semibold">1. OBJETO DEL CONTRATO</h3>
            <p class="text-gray-700">EL AHORRANTE abre una cuenta de ahorro con LA ENTIDAD, con el fin de depositar y
                administrar sus ahorros bajo las condiciones establecidas en este contrato.</p>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-semibold">2. CONDICIONES DE LA CUENTA</h3>
            <ul class="text-gray-700 list-disc list-inside">
                <li>Saldo de apertura: <strong> Q. {{ number_format($contrato->saldo_contrato, 2) }}</strong>.</li>
                <li>Tasa de interés anual: <strong>{{ $contrato->interes_anual }}%</strong>.</li>
                <li>Los retiros se regirán según las normativas vigentes.</li>
            </ul>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-semibold">3. DERECHOS Y OBLIGACIONES</h3>
            <p><strong>De EL AHORRANTE:</strong> Puede realizar depósitos y retiros bajo los términos acordados y
                recibir los intereses generados.</p>
            <p><strong>De LA ENTIDAD:</strong> Garantiza la seguridad de los fondos y administra la cuenta conforme a la
                legislación vigente.</p>
        </div>

        <div class="my-6">
            <h3 class="text-lg font-semibold">4. DISPOSICIONES GENERALES</h3>
            <p>Las partes acuerdan que cualquier controversia será resuelta conforme a la legislación vigente.</p>
        </div>
        <div class="flex items-center justify-between mt-6">
            <div class="text-center">
                <p class="font-semibold border-t border-t-indigo-500">EL AHORRANTE</p>
                <div class="w-48 mx-auto mt-8 border-t-2 border-gray-800"></div>
                <p class="mt-2 text-sm">{{ $contrato->cliente->nombre_completo }}</p>
            </div>
            <div class="text-center">
                <p class="font-semibold border-t border-t-indigo-500">REPRESENTANTE DE LA ENTIDAD</p>
                <div class="w-48 mx-auto mt-8 border-t-2 border-gray-800"></div>
                <p class="mt-2 text-sm">ORGANIZACION DE PRESTAMOS S.A.</p>
            </div>
        </div>

        <p class="mt-24 text-center text-gray-600">Fecha de Firma: <strong>{{
                \Carbon\Carbon::parse($contrato->fecha_apertura)->format('d/m/Y') }}</strong></p>
    </div>

</div>