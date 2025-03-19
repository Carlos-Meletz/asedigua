<?php

namespace App\Filament\Resources\AhmovimientoResource\Pages;

use Filament\Actions;
use App\Models\Ahmovimiento;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\AhmovimientoResource;
use Torgodly\Html2Media\Actions\Html2MediaAction;

class CreateAhmovimiento extends CreateRecord
{
    protected static string $resource = AhmovimientoResource::class;
}
