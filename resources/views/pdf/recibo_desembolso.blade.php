<div class="max-w-4xl p-6 mx-auto mt-6 bg-white rounded-lg shadow-lg">
    <h2 class="mb-4 text-2xl font-semibold text-center text-gray-800">RECIBO DE DESEMBOLSO DE CRÉDITO</h2>
    <div class="space-y-2 text-gray-700">
        <p><strong>Cliente:</strong> {{ $credito->cliente->nombre_completo }}</p>
        <p><strong>Monto Desembolsado:</strong> Q{{ number_format($credito->monto_desembolsado, 2) }}</p>
        <p><strong>Fecha de Desembolso:</strong> {{ $credito->fecha_desembolso }}</p>
        <p><strong>Número de Crédito:</strong> {{ $credito->codigo }}</p>
    </div>

    <div class="mt-4 overflow-x-auto">
        <table class="w-full border border-collapse border-gray-300">
            <thead>
                <tr class="text-gray-700 bg-gray-200">
                    <th class="px-4 py-2 border border-gray-300">Concepto</th>
                    <th class="px-4 py-2 border border-gray-300">Monto</th>
                </tr>
            </thead>
            <tbody>
                <tr class="text-center bg-white">
                    <td class="px-4 py-2 border border-gray-300">Capital</td>
                    <td class="px-4 py-2 border border-gray-300">Q{{ number_format($credito->monto_desembolsado, 2) }}
                    </td>
                </tr>
                <tr class="text-center bg-gray-100">
                    <td class="px-4 py-2 border border-gray-300">Descuentos</td>
                    <td class="px-4 py-2 border border-gray-300">Q{{ number_format($credito->descuentos, 2) }}</td>
                </tr>
                <tr class="text-center bg-gray-100">
                    <td class="px-4 py-2 border border-gray-300"><strong>Efectivo Líquido</strong></td>
                    <td class="px-4 py-2 border border-gray-300"><strong>Q{{ number_format($credito->monto_desembolsado
                            -
                            $credito->descuentos, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-6 text-gray-700">
        <p class="mb-2">Firma del Cliente: ________________________</p>
        <p>Firma del Cajero: ________________________</p>
    </div>
</div>