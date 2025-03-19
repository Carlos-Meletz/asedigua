<?php

namespace App\Filament\Resources\FondoResource\Pages;

use App\Filament\Resources\FondoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFondo extends EditRecord
{
    protected static string $resource = FondoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
