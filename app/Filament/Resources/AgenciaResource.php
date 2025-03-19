<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Agencia;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Locacion;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Dotswan\MapPicker\Fields\Map;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AgenciaResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AgenciaResource\RelationManagers;
use Filament\Forms\Components\Section;

class AgenciaResource extends Resource
{
    protected static ?string $model = Agencia::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Empresa';
    protected static ?string $navigationLabel = 'Agencias';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalles de Agencia')->schema([
                    Forms\Components\TextInput::make('nombre')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('codigo')
                        ->default(fn($livewire) => sprintf('%03d', (Agencia::max('id') ?? 0) + 1))
                        ->readOnly(),
                    Forms\Components\TextInput::make('email')
                        ->email(),
                    Forms\Components\TextInput::make('telefono')
                        ->tel()
                        ->maxLength(20),
                ])->columns(4),
                Section::make('Informacion de Ubicación')->schema([
                    Forms\Components\Select::make('departamento')
                        ->required()
                        ->options(Locacion::departamentos())
                        ->preload()
                        ->live()
                        ->afterStateUpdated(fn(callable $set) => $set('municipio', null))
                        ->native(false),
                    Forms\Components\Select::make('municipio')
                        ->options(
                            fn(callable $get) =>
                            $get('departamento')
                                ? Locacion::municipios($get('departamento'))
                                : []
                        )
                        ->required()
                        ->native(false)
                        ->reactive(),
                    Forms\Components\TextInput::make('direccion')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),
                    Forms\Components\TextInput::make('latitude')
                        ->label('Latitud')
                        ->numeric()
                        ->required()
                        ->readOnly()
                        ->reactive(),
                    Forms\Components\TextInput::make('longitude')
                        ->label('Longitud')
                        ->numeric()
                        ->readOnly()
                        ->required()
                        ->reactive(),
                    Map::make('location')
                        ->label('Location')
                        ->columnSpanFull()
                        ->defaultLocation(latitude: 14.77222, longitude: -91.18333)
                        ->afterStateUpdated(function (Set $set, ?array $state): void {
                            $set('latitude',  $state['lat'] ?? null); // Verifica si 'lat' existe
                            $set('longitude', $state['lng'] ?? null); // Verifica si 'lng' existe
                        })
                        ->afterStateHydrated(function ($state, $record, Set $set): void {
                            if ($record) {
                                $set('location', [
                                    'lat' => $record->latitude ?? 0, // Proporciona un valor predeterminado (ejemplo: 0)
                                    'lng' => $record->longitude ?? 0,
                                ]);
                            }
                        })
                        ->extraStyles([
                            'min-height: 30vh',
                            'border-radius: 50px'
                        ])
                        ->liveLocation(true, false, 5000)
                        ->showMarker()
                        ->markerColor("#22c55eff")
                        ->markerHtml('<div class="custom-marker">...</div>')
                        ->markerIconUrl('/icons/marker.png')
                        ->markerIconSize([35, 35])
                        ->markerIconClassName('my-marker-class')
                        ->markerIconAnchor([16, 32])
                        ->showFullscreenControl()
                        ->showZoomControl()
                        ->draggable()
                        ->tilesUrl("https://tile.openstreetmap.de/{z}/{x}/{y}.png")
                        ->zoom(12)
                        ->detectRetina()
                        ->showMyLocationButton()
                        ->clickable(true),
                ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departamento')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('municipio')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('direccion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgencias::route('/'),
            'create' => Pages\CreateAgencia::route('/create'),
            'edit' => Pages\EditAgencia::route('/{record}/edit'),
        ];
    }
}
