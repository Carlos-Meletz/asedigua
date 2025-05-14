<x-filament::page>
    <form wire:submit.prevent="generarReporte" class="space-y-4">
        {{ $this->form }}

        <x-filament::button type="submit">Generar Reporte</x-filament::button>
    </form>

    @if($registros->isNotEmpty())
    <div class="mt-6">
        <table class="w-full text-sm border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 border">Cliente</th>
                    <th class="p-2 border">Fecha</th>
                    <th class="p-2 border">Monto</th>
                    <th class="p-2 border">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registros as $credito)
                <tr class="odd:bg-white even:bg-gray-50">
                    <td class="p-2 border">{{ $credito->cliente->nombre }}</td>
                    <td class="p-2 border">{{ \Carbon\Carbon::parse($credito->fecha_desembolso)->format('d/m/Y') }}</td>
                    <td class="p-2 border">Q {{ number_format($credito->monto, 2) }}</td>
                    <td class="p-2 border">{{ ucfirst($credito->estado) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex justify-end mt-4 space-x-4">
            <x-filament::button wire:click="exportarPdf" icon="heroicon-m-document">Exportar PDF</x-filament::button>
            <x-filament::button wire:click="exportarExcel" icon="heroicon-m-table-cells">Exportar Excel
            </x-filament::button>
        </div>
    </div>
    @endif
</x-filament::page>