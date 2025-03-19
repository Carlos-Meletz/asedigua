<?php

namespace App\Filament\Resources\CrmovimientoResource\Pages;

use App\Filament\Resources\CrmovimientoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCrmovimiento extends EditRecord
{
    protected static string $resource = CrmovimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
