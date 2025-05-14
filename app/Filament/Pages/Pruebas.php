<?php

namespace App\Filament\Pages;

use App\Helpers\Funciones;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;

class Pruebas extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static string $view = 'filament.pages.plan-pruebas-creditos';
    protected static ?string $navigationLabel = 'Plan de Pruebas';
    protected static ?string $title = 'Simulador de Créditos';


    public ?float $monto;
    public ?int $plazo;
    public ?float $tasa;
    public ?string $tipo = 'flat';
    public ?string $fecha_desembolso = '';
    public ?string $fecha_primerpago = '';
    protected array $planPagos = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(3)
                ->schema([
                    TextInput::make('monto')
                        ->label('Monto del Crédito')
                        ->numeric()
                        ->required(),

                    TextInput::make('plazo')
                        ->label('Plazo en meses')
                        ->numeric()
                        ->required(),

                    TextInput::make('tasa')
                        ->label('Tasa de interés anual (%)')
                        ->numeric()
                        ->required(),


                    Select::make('tipo')
                        ->label('Tipo de Cuota')
                        ->options([
                            'flat' => 'Flat',
                            'frances' => 'Sistema Francés (Cuotas Fijas)',
                            'aleman' => 'Sistema Alemán (Decreciente)',
                            'americano' => 'Sistema Americano (Pago Único)',
                        ])
                        ->default('flat')
                        ->required()
                        ->native(false),

                    DatePicker::make('fecha_desembolso')
                        ->label('Fecha de desembolso')
                        ->reactive()
                        ->live(onBlur: true)
                        ->afterStateUpdated(
                            function (callable $set, callable $get) {
                                Funciones::calcularFechaPrimerPago($get, $set);
                            }
                        )
                        ->required(),
                    DatePicker::make('fecha_primerpago')
                        ->label('Fecha del Primer Pago')
                        ->required(),
                ]),
        ];
    }

    public function simular()
    {
        $this->validate();
        // $this->planPagos[] = [];

        $monto = $this->monto;
        $plazo = $this->plazo;
        $tasaAnual = $this->tasa;
        $fecha = Carbon::parse($this->fecha_desembolso);
        $fechaPrimerPago = Carbon::parse($this->fecha_primerpago);
        $saldo = $monto;

        $tasaMensual = ($tasaAnual / 100) / 12;
        $tasaDiaria = ($tasaAnual / 100) / 365; // Para cálculo de interés diario (360 días año comercial)
        $this->planPagos = [];
        $diasAdicionales = 0;

        // Fecha esperada para un mes exacto
        $fechaUnMesDespues = $fecha->copy()->addMonth();

        // Calcular la diferencia real de días
        $diasDiferencia = $fecha->diffInDays($fechaPrimerPago);

        // Calcular días adicionales (más allá de 1 mes exacto)
        $diasAdicionales = $fechaPrimerPago->gt($fechaUnMesDespues)
            ? $fechaUnMesDespues->diffInDays($fechaPrimerPago)
            : 0;
        $interesExtra = $saldo * $tasaDiaria * $diasAdicionales;


        for ($i = 1; $i <= $plazo; $i++) {
            if ($this->tipo === 'frances') {
                $cuota = ($monto * $tasaMensual) / (1 - pow(1 + $tasaMensual, -$plazo));
                $capital =  $cuota - ($saldo * $tasaMensual);
                $interes =  $saldo * $tasaMensual;
            } elseif ($this->tipo === 'aleman') {
                $cuota = $saldo / ($plazo - ($i - 1)) + ($saldo * $tasaMensual);
                $capital =  $cuota - ($saldo * $tasaMensual);
                $interes =  $saldo * $tasaMensual;
            } elseif ($this->tipo === 'americano') {
                $interes = $saldo * $tasaMensual;
                $capital = ($i == $plazo) ? $saldo : 0;
                $cuota = $interes + $capital;
            } elseif ($this->tipo === 'flat') {
                $interes = $monto * $tasaMensual;
                $capital = $monto / $plazo;
                $cuota = $capital + $interes;
            }
            // Para el primer pago, agregamos el interés extra si hay
            if ($i == 1 && $interesExtra > 0) {
                $cuota += $interesExtra;
                $interes += $interesExtra;
            }
            // Calcular saldo restante
            $saldo -= $capital;
            $fechaPago = $fechaPrimerPago->copy()->addMonths($i - 1);

            $this->planPagos[] = [
                'nro_cuota' => $i,
                'fecha' => $fechaPago->format('d-m-Y'),
                'cuota' => round($cuota, 2),
                'capital' => round($capital, 2),
                'interes' => round($interes, 2),
                'saldo' => max(round($saldo, 2), 0),
            ];
        }
    }

    public function descargarPdf()
    {

        // Asegúrate de que el plan esté generado
        if (empty($this->planPagos)) {
            $this->simular(); // genera el plan si no existe
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.plan-pruebas', [
            'planPagos' => $this->planPagos,
            'monto' => $this->monto,
            'plazo' => $this->plazo,
            'tasa' => $this->tasa,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'plan_pagos.pdf');
    }
}
