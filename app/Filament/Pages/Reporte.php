<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Agencia;
use App\Models\Credito;
use App\Models\Destino;
use App\Models\Crelinea;
use App\Models\Empleado;
use Filament\Pages\Page;
use App\Helpers\Funciones;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;

class Reporte extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.reportes';
    protected static ?string $navigationLabel = 'Reportes';
    protected static ?string $navigationGroup = 'Créditos';
    protected static ?string $title = 'Reporte de Créditos  -- !! EN MANTENIMIENTO O PRUEBAS - NO FUNCIONAL !!';
    public Collection $registros;

    public ?string $agencia = null;
    public ?string $asesor = null;
    public ?string $destino = null;
    public ?string $linea = null;


    public function mount(): void
    {
        $this->registros = collect();
    }
    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('fechaInicio')->label('Desde'),
            DatePicker::make('fechaFin')->label('Hasta'),

            Select::make('agencia')
                ->label('Agencia')
                ->options(Agencia::all()->pluck('nombre', 'id'))
                ->searchable()
                ->placeholder('Todas'),

            // Select::make('asesor')
            //     ->label('Asesor')
            //     ->options(Empleado::all()->pluck('nombre', 'id'))
            //     ->searchable()
            //     ->placeholder('Todos'),

            Select::make('destino')
                ->label('Destino del crédito')
                ->options(Destino::all()->pluck('nombre', 'id'))
                ->searchable()
                ->placeholder('Todos'),

            Select::make('linea')
                ->label('Línea de crédito')
                ->options(Crelinea::all()->pluck('nombre', 'id'))
                ->searchable()
                ->placeholder('Todas'),

            Select::make('estado')
                ->label('Estado')
                ->options([
                    'pendiente' => 'Pendiente',
                    'pagado' => 'Pagado',
                    'mora' => 'En Mora',
                ])
                ->placeholder('Todos'),
        ];
    }

    public function generarReporte(): void
    {
        $query = Credito::query();

        if ($this->fechaInicio) {
            $query->whereDate('fecha_desembolso', '>=', $this->fechaInicio);
        }

        if ($this->fechaFin) {
            $query->whereDate('fecha_desembolso', '<=', $this->fechaFin);
        }

        if ($this->estado) {
            $query->where('estado', $this->estado);
        }

        if ($this->agencia) {
            $query->where('agencia_id', $this->agencia);
        }

        if ($this->asesor) {
            $query->where('asesor_id', $this->asesor);
        }

        if ($this->destino) {
            $query->where('destino_id', $this->destino);
        }

        if ($this->linea) {
            $query->where('linea_credito_id', $this->linea);
        }

        $this->registros = $query->get();
    }


    // public function exportarExcel()
    // {
    //     return Excel::download(new ReporteCreditosExport($this->registros), 'reporte-creditos.xlsx');
    // }

    public function exportarPdf()
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.reporte-creditos', ['registros' => $this->registros]);
        return response()->streamDownload(fn() => print($pdf->stream()), 'reporte-creditos.pdf');
    }
}
