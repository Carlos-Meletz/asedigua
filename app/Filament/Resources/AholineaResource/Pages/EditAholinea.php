<?php

namespace App\Filament\Resources\AholineaResource\Pages;

use App\Filament\Resources\AholineaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAholinea extends EditRecord
{
    protected static string $resource = AholineaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
