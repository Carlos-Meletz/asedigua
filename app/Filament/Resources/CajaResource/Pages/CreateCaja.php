<?php

namespace App\Filament\Resources\CajaResource\Pages;

use App\Models\Caja;
use Filament\Actions;
use App\Models\Agencia;
use App\Filament\Resources\CajaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCaja extends CreateRecord
{
    protected static string $resource = CajaResource::class;
}
