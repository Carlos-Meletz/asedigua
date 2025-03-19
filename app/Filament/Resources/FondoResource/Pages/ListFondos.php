<?php

namespace App\Filament\Resources\FondoResource\Pages;

use App\Filament\Resources\FondoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFondos extends ListRecords
{
    protected static string $resource = FondoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
