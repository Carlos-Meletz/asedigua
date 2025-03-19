<?php

namespace App\Filament\Resources\AhmovimientoResource\Pages;

use App\Filament\Resources\AhmovimientoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAhmovimientos extends ListRecords
{
    protected static string $resource = AhmovimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
