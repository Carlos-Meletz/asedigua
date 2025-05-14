<x-filament::page>
    <form wire:submit.prevent="simular" class="space-y-6">
        <div class="p-6 bg-white border rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button type="submit" size="lg" color="primary" class="w-full">
                    Simular Plan de Pagos
                </x-filament::button>
            </div>
        </div>
    </form>

    @if (!empty($this->planPagos))
    <div class="mt-10">
        <div class="overflow-x-auto bg-white border rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
            <table class="w-full text-sm divide-y divide-gray-200 dark:divide-gray-600">
                <thead class="bg-primary-50 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                    <tr>
                        <th class="px-6 py-3 font-semibold text-left"># Cuota</th>
                        <th class="px-6 py-3 font-semibold text-left">Fecha</th>
                        <th class="px-6 py-3 font-semibold text-left">Cuota</th>
                        <th class="px-6 py-3 font-semibold text-left">Capital</th>
                        <th class="px-6 py-3 font-semibold text-left">Interés</th>
                        <th class="px-6 py-3 font-semibold text-left">Saldo</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100 dark:bg-gray-700 dark:divide-gray-600">
                    @foreach ($this->planPagos as $pago)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-600 dark:text-gray-400">
                        <td class="px-6 py-2 whitespace-nowrap">{{ $pago['nro_cuota'] }}</td>
                        <td class="px-6 py-2 whitespace-nowrap">{{ $pago['fecha'] }}</td>
                        <td class="px-6 py-2 font-semibold text-green-700 dark:text-green-400 whitespace-nowrap">Q{{
                            number_format($pago['cuota'], 2) }}</td>
                        <td class="px-6 py-2 whitespace-nowrap">Q{{ number_format($pago['capital'], 2) }}</td>
                        <td class="px-6 py-2 text-blue-600 dark:text-blue-400 whitespace-nowrap">Q{{
                            number_format($pago['interes'], 2) }}</td>
                        <td class="px-6 py-2 whitespace-nowrap">Q{{ number_format($pago['saldo'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end mt-6 space-x-4">
            <x-filament::button wire:click="descargarPdf" size="lg" icon="heroicon-m-arrow-down-tray">
                Descargar PDF
            </x-filament::button>
        </div>
    </div>
    @endif
</x-filament::page>