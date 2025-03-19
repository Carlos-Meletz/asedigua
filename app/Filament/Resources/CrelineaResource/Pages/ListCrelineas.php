<?php

namespace App\Filament\Resources\CrelineaResource\Pages;

use App\Filament\Resources\CrelineaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCrelineas extends ListRecords
{
    protected static string $resource = CrelineaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
