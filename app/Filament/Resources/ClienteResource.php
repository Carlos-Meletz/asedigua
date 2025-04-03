<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Cliente;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Locacion;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Dotswan\MapPicker\Fields\Map;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ClienteResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ClienteResource\RelationManagers;
use App\Filament\Resources\ClienteResource\RelationManagers\ReferenciasRelationManager;
use App\Filament\Resources\ClienteResource\RelationManagers\TrabajosRelationManager;
use App\Filament\Resources\ClienteResource\RelationManagers\ViviendasRelationManager;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class ClienteResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'viviendasCrear',
            'viviendasVer',
            'viviendasEditar',
            'viviendasEliminar',
            'referenciasCrear',
            'referenciasVer',
            'referenciasEditar',
            'referenciasEliminar',
            'trabajosCrear',
            'trabajosVer',
            'trabajosEditar',
            'trabajosEliminar',
        ];
    }
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-group';
    protected static ?string $navigationLabel = 'Registro Clientes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Datos Personales')
                        ->schema([
                            Forms\Components\FileUpload::make('fotografia')
                                // ->required()
                                ->image()
                                ->maxSize(1024)
                                ->imageEditor()
                                ->openable()
                                ->disk('cliente')
                                ->imageCropAspectRatio('1:1')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('nombre')
                                ->required()
                                ->maxLength(50)
                                ->columnSpan(2),
                            Forms\Components\TextInput::make('apellido')
                                ->required()
                                ->maxLength(50)
                                ->columnSpan(2),
                            Forms\Components\DatePicker::make('fecha_nacimiento')
                                ->default(Carbon::now())
                                ->native(false)
                                ->required()
                                ->maxDate((Carbon::now()->year) - 16)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $edad = (Carbon::now()->year) - (Carbon::parse($state)->year);
                                    $set('edad', $edad);
                                }),
                            Forms\Components\TextInput::make('edad')
                                ->required()
                                ->suffix('años.')
                                ->readOnly(),
                            Forms\Components\Select::make('genero')
                                ->options([
                                    'masculino' => 'Masculino',
                                    'femenino' => 'Femenino',
                                ])
                                ->native(false)
                                ->required(),
                            Forms\Components\TextInput::make('dpi')
                                ->label('Número de CUI')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(20)
                                ->mask('9999 99999 9999')
                                ->placeholder('0000 00000 0000')
                                ->validationMessages([
                                    'unique' => 'El :attribute ya está registrado.',
                                ]),
                            Forms\Components\Select::make('dpi_dep')
                                ->label('DPI - Departamento extendido')
                                ->required()
                                ->options(Locacion::departamentos())
                                ->preload()
                                ->live()
                                ->afterStateUpdated(fn(callable $set) => $set('dpi_mun', null)) // Resetea el municipio al cambiar el departamento,
                                ->native(false),
                            Forms\Components\Select::make('dpi_mun')
                                ->label('DPI - Municipio extendido')
                                ->options(
                                    fn(callable $get) =>
                                    $get('dpi_dep')
                                        ? Locacion::municipios($get('dpi_dep'))
                                        : []
                                )
                                ->required()
                                ->native(false)
                                ->reactive(),
                            Forms\Components\Select::make('estado_civil')
                                ->options([
                                    'Masculino' => [
                                        'soltero' => 'Soltero',
                                        'casado' => 'Casado',
                                        'unido' => 'Unido',
                                        'divorciado' => 'Divorciado',
                                        'viudo' => 'Viudo',
                                    ],
                                    'Femenino' => [
                                        'soltera' => 'Soltera',
                                        'casada' => 'Casada',
                                        'unida' => 'Unida',
                                        'divorciada' => 'Divorciada',
                                        'viuda' => 'Viuda',
                                    ],
                                ])
                                ->native(false)
                                ->required(),
                            Forms\Components\Select::make('estado')
                                ->options([
                                    'activo' => 'Activo',
                                    'inactivo' => 'Inactivo',
                                    'suspendido' => 'Suspendido',
                                    'bloqueado' => 'Bloqueado',
                                ])
                                ->default('activo')
                                ->required()
                                ->native(false)
                                ->visibleOn('edit'),
                        ])->columns(4),
                    Wizard\Step::make('Contacto')
                        ->schema([
                            Forms\Components\TextInput::make('telefono')
                                ->tel()
                                ->required()
                                ->maxLength(20),
                            Forms\Components\TextInput::make('celular')
                                ->tel()
                                ->maxLength(20),
                            Forms\Components\TextInput::make('correo')
                                ->email()
                                ->maxLength(50),
                            Forms\Components\Toggle::make('social')
                                ->inline(false)
                                ->required(),
                            Forms\Components\FileUpload::make('archivos')
                                ->label('Archivo')
                                ->disk('archivo')
                                ->maxSize(1024)
                                ->multiple() // Carpeta de almacenamiento
                                // ->required()
                                ->image()
                                ->maxFiles(5)
                                ->imageEditor()
                                ->panelLayout('grid')
                                ->openable()
                                ->columnSpanFull(),
                        ])->columns(4),
                    Wizard\Step::make('Domicilio')
                        ->schema([
                            Forms\Components\Select::make('departamento')
                                ->required()
                                ->options(Locacion::departamentos())
                                ->preload()
                                ->live()
                                ->afterStateUpdated(fn(callable $set) => $set('municipio', null)) // Resetea el municipio al cambiar el departamento,
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
                            Forms\Components\Textarea::make('notas')
                                ->columnSpan(2),
                            Forms\Components\TextInput::make('latitude')
                                ->label('Latitud')
                                ->numeric()
                                ->required()
                                ->reactive(),
                            Forms\Components\TextInput::make('longitude')
                                ->label('Longitud')
                                ->numeric()
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
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre_completo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('apellido')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('edad')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('genero')
                    ->formatStateUsing(fn($state) => ucwords(strtolower($state)))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('dpi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dpi_dep')
                    ->formatStateUsing(fn($state) => Locacion::find($state)->name)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('dpi_mun')
                    ->formatStateUsing(fn($state) => Locacion::find($state)->name)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('estado_civil')
                    ->formatStateUsing(fn($state) => ucwords(strtolower($state)))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\ImageColumn::make('fotografia')
                    ->label('Foto')
                    ->disk('cliente')
                    ->circular(),
                Tables\Columns\TextColumn::make('telefono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('celular')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('correo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('social')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('departamento')
                    ->formatStateUsing(fn($state) => Locacion::find($state)->name)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('municipio')
                    ->formatStateUsing(fn($state) => Locacion::find($state)->name)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('direccion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'activo' => 'success',
                        'inactivo' => 'warning',
                        'suspendido' => 'gray',
                        'bloqueado' => 'danger',
                    }),
                Tables\Columns\ImageColumn::make('archivos')
                    ->disk('archivo')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText(),
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
            ViviendasRelationManager::class,
            TrabajosRelationManager::class,
            ReferenciasRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'view' => Pages\ViewCliente::route('/{record}'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
}
