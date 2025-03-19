<?php

namespace App\Filament\Resources\AhorroResource\Pages;

use App\Filament\Resources\AhorroResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAhorro extends EditRecord
{
    protected static string $resource = AhorroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    // public function getRelationManagers(): array
    // {
    //     return [];
    // }
}
