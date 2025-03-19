<?php

namespace App\Filament\Resources\AhorroResource\Pages;

use App\Filament\Resources\AhorroResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAhorros extends ListRecords
{
    protected static string $resource = AhorroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
