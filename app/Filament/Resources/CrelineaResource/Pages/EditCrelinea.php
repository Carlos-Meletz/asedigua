<?php

namespace App\Filament\Resources\CrelineaResource\Pages;

use App\Filament\Resources\CrelineaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCrelinea extends EditRecord
{
    protected static string $resource = CrelineaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
