@php
use App\Models\Empresa;
use Carbon\Carbon;

$empresa = Empresa::first();
@endphp

@props(['agencia'])

{{--
<hr class="w-full h-4 bg-gray-200 "> --}}
<header class="flex justify-between py-6 bg-white shadow-md">
    {{-- <div class="flex items-center space-x-4"> --}}
        {{-- Logo de la empresa --}}
        {{-- @if($empresa->logo) --}}
        {{-- <img src="{{ Storage::url('logo/' . $empresa->logo) }}" alt="Logo" class="h-16">
        @else
        <span class="text-gray-500">Sin Logo</span>
        @endif
        <div class="px-4">
            <h1 class="text-xl font-bold text-blue-700">{{ $empresa->nombre ?? 'Nombre de la Empresa' }}</h1>
            <p class="text-xs text-gray-600">{{ $agencia->direccion ?? 'Dirección de la Agencia' }}</p>
            <p class="text-xs text-gray-600">TEL: {{ $agencia->telefono ?? 'N/A' }}</p>
        </div>
    </div> --}}

    <div class="w-full text-xs font-semibold text-right text-gray-600">
        <p>{{ Carbon::now() }}</p>
        {{-- <p>{{ $agencia->nombre }}</p> --}}
        <p>{{ Auth::user()->username }}</p>
    </div>
</header>