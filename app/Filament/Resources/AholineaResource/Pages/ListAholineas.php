<?php

namespace App\Filament\Resources\AholineaResource\Pages;

use App\Filament\Resources\AholineaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAholineas extends ListRecords
{
    protected static string $resource = AholineaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
