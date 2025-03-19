<?php

namespace App\Filament\Resources\CrmovimientoResource\Pages;

use App\Filament\Resources\CrmovimientoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCrmovimientos extends ListRecords
{
    protected static string $resource = CrmovimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
