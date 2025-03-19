<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Livewire\TemporaryUploadedFile;
use Filament\Forms\Components\Select;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;


class Empresa extends Page implements HasForms
{
    public ?array $data = [];
    use InteractsWithForms;
    protected static string $view = 'filament.pages.empresa';

    protected static ?string $navigationLabel = 'Información';
    protected static ?string $title = 'Datos de la Empresa';
    protected static ?string $navigationGroup = 'Empresa';


    public function mount(): void
    {
        $empresa = \App\Models\Empresa::first();

        // Si existe, llenar el formulario con los datos
        if ($empresa) {
            $this->form->fill($empresa->toArray());
        } else {
            $this->form->fill();
        };
    }
    public function save(): void
    {
        try {
            $data = $this->form->getState();
            // Buscar el primer registro de la empresa o crear uno nuevo si no existe
            $empresa = \App\Models\Empresa::firstOrCreate([]);

            // Actualizar la empresa con los datos del formulario
            $empresa->update($data);

            // $this->save($data);
        } catch (Halt $exception) {
            return;
        }
        Notification::make()
            ->success()
            ->title(__('filament-panels::resources/pages/edit-record.notifications.saved.title'))
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),

                TextInput::make('razon_social')
                    ->maxLength(255),

                TextInput::make('nit')
                    ->required()
                    ->maxLength(255),

                Select::make('tipo_empresa')
                    ->options([
                        'S.A.' => 'Sociedad Anónima',
                        'S.R.L.' => 'Sociedad de Responsabilidad Limitada',
                        'Otro' => 'Otro',
                    ])
                    ->required(),

                DatePicker::make('fecha_constitucion'),

                TextInput::make('direccion_fiscal')
                    ->required()
                    ->maxLength(255),

                Section::make('Representante Legal')
                    ->schema([
                        TextInput::make('rps_nombre')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('rps_dpi')
                            ->required()
                            ->maxLength(20),

                        TextInput::make('rps_dpiDep')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('rps_dpiMun')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('rps_cargo')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('rps_profesion')
                            ->required()
                            ->maxLength(255),

                        DatePicker::make('rps_fechaNac')
                            ->required(),

                        TextInput::make('rps_edad')
                            ->numeric()
                            ->required(),

                        Select::make('rps_estado_civil')
                            ->options([
                                'Soltero' => 'Soltero',
                                'Casado' => 'Casado',
                                'Divorciado' => 'Divorciado',
                                'Viudo' => 'Viudo',
                            ])
                            ->required(),

                        TextInput::make('rps_direccion')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('rps_telefono')
                            ->tel()
                            ->maxLength(20),
                    ]),

                FileUpload::make('logo')
                    ->required()
                    ->image()
                    ->maxSize(1024)
                    ->imageEditor()
                    ->openable()
                    ->disk('logo')
                    ->imageCropAspectRatio('1:1'),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->submit('save'),
        ];
    }
}
