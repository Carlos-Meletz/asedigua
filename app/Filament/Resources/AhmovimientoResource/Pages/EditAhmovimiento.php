<?php

namespace App\Filament\Resources\AhmovimientoResource\Pages;

use App\Filament\Resources\AhmovimientoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAhmovimiento extends EditRecord
{
    protected static string $resource = AhmovimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
