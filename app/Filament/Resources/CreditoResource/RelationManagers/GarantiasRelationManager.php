<?php

namespace App\Filament\Resources\CreditoResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Set;
use App\Models\Garantia;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Dotswan\MapPicker\Fields\Map;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class GarantiasRelationManager extends RelationManager
{
    protected static string $relationship = 'garantias';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos de la persona que firmara con solicitante')->schema([
                    Select::make('anterior')
                        ->label('Garantias anteriores del Cliente')
                        ->options(function (RelationManager $livewire) {
                            $credito = $livewire->getOwnerRecord();
                            $clienteId = $credito?->cliente_id;

                            return Garantia::whereHas('credito', function ($query) use ($clienteId, $credito) {
                                $query->where('cliente_id', $clienteId)
                                    ->where('id', '!=', $credito->id);
                            })
                                ->get()
                                ->groupBy('dpi')  // Agrupa por DPI
                                ->map(fn($group) => $group->first())
                                ->pluck('tipo_garantia', 'id');
                        })
                        ->native(false)
                        ->searchable()
                        ->optionsLimit(5)
                        ->preload()
                        ->reactive()
                        ->columnSpanFull()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (!$state) {
                                // Limpiar todos los campos si no se selecciona un fiador
                                $set('tipo_garantia', null);
                                $set('Descriptor', null);
                                $set('valor_estimado', null);
                                $set('descripcion', null);
                                $set('observaciones', null);
                                $set('numero_documento', null);
                                $set('latitude', null);
                                $set('longitude', null);
                                $set('ubicacion', null);
                                $set('superficie', null);
                                $set('registro_propiedad', null);
                                $set('nombre_propietario', null);
                                $set('documentos', null);
                                $set('notario_responsable', null);
                                $set('fecha_registro', null);
                                // return;
                            }

                            $garantia = Garantia::find($state);
                            if ($garantia) {
                                $set('tipo_garantia', $garantia->tipo_garantia);
                                $set('Descriptor', $garantia->Descriptor);
                                $set('valor_estimado', $garantia->valor_estimado);
                                $set('descripcion', $garantia->descripcion);
                                $set('observaciones', $garantia->observaciones);
                                $set('numero_documento', $garantia->numero_documento);
                                $set('latitude', $garantia->latitude);
                                $set('longitude', $garantia->longitude);
                                $set('ubicacion', $garantia->ubicacion);
                                $set('superficie', $garantia->superficie);
                                $set('registro_propiedad', $garantia->registro_propiedad);
                                $set('nombre_propietario', $garantia->nombre_propietario);
                                $set('documentos', $garantia->documentos);
                                $set('notario_responsable', $garantia->notario_responsable);
                                $set('fecha_registro', $garantia->fecha_registro);
                            }
                        }),
                    Select::make('tipo_garantia')
                        ->label('Tipo de Garantía')
                        ->reactive()
                        ->options([
                            'solidario' => 'SOLIDARIO',
                            'fiduciario' => 'FIDUCIARIO',
                            'hipotecario' => 'HIPOTECARIO',
                            'prendario' => 'PRENDARIO',
                            'otro' => 'OTRO',
                        ])
                        ->searchable()
                        ->required(),

                    Select::make('Descriptor')
                        ->label('Descriptor')
                        ->options([
                            'carta_venta' => 'CARTA DE VENTA',
                            'copia_simple' => 'COPIA SIMPLE LEGALIZADA',
                            'declaracion_jurada' => 'DECLARACION JURADA',
                            'factura' => 'FACTURA',
                            'factura_conformada' => 'FACTURA CONFORMADA',
                            'primer_testimonio' => 'PRIMER TESTIMONIO',
                            'registro_publico' => 'REGISTRO PUBLICO',
                            'registro_vehicular' => 'REGISTRO VEHICULAR',
                            'otros' => 'OTROS',
                        ])
                        ->nullable(),

                    TextInput::make('valor_estimado')
                        ->label('Valor Estimado')
                        ->numeric()
                        ->prefix('Q')
                        ->required(),
                    TextInput::make('observaciones')
                        ->label('Observaciones')
                        ->nullable(),

                    RichEditor::make('descripcion')
                        ->label('Descripción')
                        ->required()
                        ->columnSpanFull(),

                    Section::make('Datos de garantía especifica')->schema([
                        TextInput::make('numero_documento')
                            ->label('Número de Documento')
                            ->nullable(),

                        TextInput::make('ubicacion')
                            ->label('Ubicación')
                            ->nullable()
                            ->columnSpan(3),
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
                        DatePicker::make('fecha_registro')
                            ->label('Fecha de Registro')
                            ->nullable(),
                        TextInput::make('superficie')
                            ->label('Superficie (m²)')
                            ->numeric()
                            ->suffix('m²')
                            ->nullable(),

                        TextInput::make('registro_propiedad')
                            ->label('Registro de Propiedad')
                            ->nullable(),

                        TextInput::make('nombre_propietario')
                            ->label('Nombre del Propietario')
                            ->nullable()
                            ->columnSpan(2),
                        TextInput::make('notario_responsable')
                            ->label('Notario Responsable')
                            ->nullable()
                            ->columnSpan(2),
                        Forms\Components\FileUpload::make('documentos')
                            ->label('Documentos')
                            ->disk('documento')
                            ->maxSize(1024)
                            ->multiple()
                            ->required()
                            ->image()
                            ->maxFiles(5)
                            ->imageEditor()
                            ->panelLayout('grid')
                            ->openable()
                            ->columnSpanFull(),
                    ])->columns(4)
                        ->visible(fn($get) => in_array($get('tipo_garantia'), ['prendario', 'hipotecario'])),
                ])->columns(4),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tipo_garantia')
            ->columns([
                TextColumn::make('tipo_garantia')
                    ->label('Tipo de Garantía')
                    ->formatStateUsing(fn($state) => ucwords(strtolower($state))),

                TextColumn::make('valor_estimado')
                    ->label('Valor Estimado')
                    ->money('GTQ'),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->authorize(fn() => Gate::allows('garantiaCrear_credito')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->authorize(fn() => Gate::allows('garantiaVer_credito')),
                Tables\Actions\EditAction::make()->authorize(fn() => Gate::allows('garantiaEditar_credito')),
                Tables\Actions\DeleteAction::make()->authorize(fn() => Gate::allows('garantiaEliminar_credito')),
            ])
            ->bulkActions([]);
    }
}
