<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Caja;
use App\Models\Ahorro;
use App\Models\Credito;
use App\Models\Ahmovimiento;
use App\Models\Crmovimiento;
use App\Models\Crelineacosto;
use Illuminate\Support\Facades\Gate;
use Filament\Notifications\Notification;
use Torgodly\Html2Media\Actions\Html2MediaAction;

class Funciones
{
    public static function cajaActiva($state, callable $set)
    {
        $cajaActiva = Caja::where('agencia_id', $state)->where('abierta', true)->first();
        if ($cajaActiva) {
            $set('caja_id', $cajaActiva->id);
        } else {
            $set('caja_id', '');
            Notification::make()->title('No hay caja abierta')->warning()->body('No hay una caja activa para esta agencia.')->send();
        }
    }

    public static function actualizarSaldoAh($get, $set)
    {
        if ($get('ahorro_id')) {
            $saldo = Ahorro::find($get('ahorro_id'))->saldo;
        } else {
            $saldo = $get('saldo');
        }
        $saldo = $saldo + $get('deposito') - $get('retiro') - $get('penalizacion');
        $set('saldo', $saldo);
    }

    public static function calcularInteresAh($get, $set)
    {
        if (!$get('ahorro_id')) {
            $set('int', 'Interes Acumulado: 0');
            return;
        }

        $ultimoMovimiento = Ahmovimiento::where('ahorro_id', $get('ahorro_id'))
            ->where('anulado', false) // Excluir movimientos anulados
            ->whereNull('deleted_at') // Excluir eliminados por soft delete
            ->latest('fecha') // Ordenar por la fecha más reciente
            ->first();

        if ($ultimoMovimiento) {
            $fechaUltimoMovimiento = Carbon::parse($ultimoMovimiento->fecha);
        } else {
            $fechaUltimoMovimiento = Carbon::now(); // Si no hay movimientos, tomar la fecha actual
        }

        // Calcular diferencia de días
        $diasDiferencia = intval($fechaUltimoMovimiento->diffInDays($get('fecha')));

        // Obtener la tasa de interés de la cuenta
        $cuenta = Ahorro::find($get('ahorro_id'));
        $tasaInteresDiaria = $cuenta->interes_anual / 365; // Supongamos que es anual y lo dividimos entre 365

        // Calcular el interés a pagar
        $interesCalculado = (($cuenta->saldo * $tasaInteresDiaria * $diasDiferencia) / 100);
        $interesAcumulado = round($interesCalculado + $cuenta->interes_acumulado - $get('interes'), 2);

        // Asignar el interés calculado al campo de texto
        $set('int', 'Generado (' . $diasDiferencia . ' días): Q ' . round($interesCalculado, 2) . ' + (Acumulado) Q ' . $cuenta->interes_acumulado . ' = Q ' . $interesAcumulado);
        $set('interes_acumulado', $interesAcumulado);
    }


    public static function obtenerPlan($record)
    {
        $monto = $record->monto_solicitado;
        $interesAnual = $record->interes_anual;
        $plazo = $record->plazo;
        $tipoPlazo = $record->tipo_plazo;
        $tipoCuota = $record->tipo_cuota;
        $fechaDesembolso = $record->fecha_desembolso;
        if ($monto && $interesAnual && $plazo && $tipoCuota && $fechaDesembolso) {

            $plan = [];
            $interesMensual = ($interesAnual / 100) / 12;
            $fechaPago = Carbon::parse($fechaDesembolso);
            $saldo = $monto;

            for ($i = 1; $i <= $plazo; $i++) {

                if ($tipoCuota === 'frances') {
                    $cuota = ($monto * $interesMensual) / (1 - pow(1 + $interesMensual, -$plazo));
                    $capital =  $cuota - ($saldo * $interesMensual);
                    $interes =  $saldo * $interesMensual;
                }
                if ($tipoCuota === 'aleman') {
                    $cuota = $saldo / ($plazo - ($i - 1)) + ($saldo * $interesMensual);
                    $capital =  $cuota - ($saldo * $interesMensual);
                    $interes =  $saldo * $interesMensual;
                }
                if ($tipoCuota === 'americano') {
                    $cuota = ($i === $plazo) ? $saldo + ($saldo * $interesMensual * $plazo) : ($saldo * $interesMensual);
                    if ($i == $plazo) {
                        $cuota = $saldo + ($saldo * $interesMensual);
                    }
                    $capital =  $cuota - ($saldo * $interesMensual);
                    $interes =  $saldo * $interesMensual;
                }
                if ($tipoCuota === 'flat') {
                    $cuota = ($monto + ($monto * (($interesAnual / 100) / 12) * $plazo)) / $plazo;
                    $capital =  $cuota - ($monto * $interesMensual);
                    $interes =  $monto * $interesMensual;
                }

                // Calcular saldo restante
                $saldo -= $capital;

                $fechaPago = $fechaPago->addMonth();

                $plan[] = [
                    'nocuota' => $i,
                    'fecha' => $fechaPago->format('Y-m-d'),
                    'cuota' => round($cuota, 2),
                    'interes' => round($interes, 2),
                    'capital' => round($capital, 2),
                    'saldo' => round($saldo, 2),
                ];
            }
            return $plan;
        }
        return;
    }

    public static function calcularPlanPagos(callable $get, $set)
    {
        $monto = $get('monto_solicitado');
        $interesAnual = $get('interes_anual');
        $plazo = $get('plazo');
        $tipoCuota = $get('tipo_cuota');
        $fechaDesembolso = $get('fecha_desembolso');
        if ($monto && $interesAnual && $plazo && $tipoCuota && $fechaDesembolso) {

            $plan = [];
            $interesMensual = $interesAnual / 12 / 100;
            $fechaPago = Carbon::parse($fechaDesembolso);
            $saldo = $monto;

            for ($i = 1; $i <= $plazo; $i++) {

                if ($tipoCuota === 'frances') {
                    $cuota = ($monto * $interesMensual) / (1 - pow(1 + $interesMensual, -$plazo));
                    $capital =  $cuota - ($saldo * $interesMensual);
                    $interes =  $saldo * $interesMensual;
                }
                if ($tipoCuota === 'aleman') {
                    $cuota = $saldo / ($plazo - ($i - 1)) + ($saldo * $interesMensual);
                    $capital =  $cuota - ($saldo * $interesMensual);
                    $interes =  $saldo * $interesMensual;
                }
                if ($tipoCuota === 'americano') {
                    $cuota = ($i === $plazo) ? $saldo + ($saldo * $interesMensual * $plazo) : ($saldo * $interesMensual);
                    if ($i == $plazo) {
                        $cuota = $saldo + ($saldo * $interesMensual);
                    }
                    $capital =  $cuota - ($saldo * $interesMensual);
                    $interes =  $saldo * $interesMensual;
                }
                if ($tipoCuota === 'flat') {
                    $cuota = ($monto + ($monto * (($interesAnual / 100) / 12) * $plazo)) / $plazo;
                    $capital =  $cuota - ($monto * $interesMensual);
                    $interes =  $monto * $interesMensual;
                }

                // Calcular saldo restante
                $saldo -= $capital;
                // Determinar fecha de pago según tipo de plazo
                if ($i == 1) $set('cuota', round($cuota, 2));

                $fechaPago = $fechaPago->addMonth();

                $plan[] = [
                    'nocuota' => $i,
                    'fecha' => $fechaPago->format('Y-m-d'),
                    'cuota' => round($cuota, 2),
                    'interes' => round($interes, 2),
                    'capital' => round($capital, 2),
                    'saldo' => round($saldo, 2),
                ];
            }
            return $plan;
        }
        return;
    }
    public static function obtenerHistorial(callable $get, $set)
    {
        $creditoId = $get('credito_id');
        if (!$creditoId) {
            return [];
        }

        $movimientos = Crmovimiento::where('credito_id', $creditoId)
            ->where('anulado', false)
            ->orderBy('fecha', 'asc')
            ->get();

        $plan = [];
        foreach ($movimientos as $mov) {
            $plan[] = [
                'fcontable' => Carbon::parse($mov->created_at)->format('Y-m-d'),
                'fvalor' => Carbon::parse($mov->fecha)->format('Y-m-d'),
                'atraso' => $mov->atraso,
                'pago' => round($mov->pago, 2),
                'capital' => round($mov->capital, 2),
                'interes' => round($mov->interes, 2),
                'mora' => round($mov->mora, 2),
                'otros' => round($mov->otros, 2),
                'saldo' => round($mov->saldocap, 2),
            ];
        }
        return $plan;
    }

    public static function calcularCuota(callable $get, callable $set)
    {
        $interesMensual = ($get('interes_anual') / 100) / 12;
        if ($get('tipo_cuota') === 'frances') {
            $cuota = ($get('monto_aprobado') * $interesMensual) / (1 - pow(1 + $interesMensual, -$get('plazo')));
        }
        if ($get('tipo_cuota') === 'aleman') {
            $cuota = $get('monto_aprobado') / $get('plazo') + ($get('monto_aprobado') * $interesMensual);
        }
        if ($get('tipo_cuota') === 'americano') {
            $cuota = ($get('monto_aprobado') * $interesMensual);
        }
        if ($get('tipo_cuota') === 'flat') {
            $cuota = ($get('monto_aprobado') + ($get('monto_aprobado') * ($interesMensual))) / $get('plazo');
        }
        $set('cuota', round($cuota, 2));
    }

    public static function calcularFechaPrimerPago(callable $get, callable $set)
    {
        $fechaDesembolso = $get('fecha_desembolso');
        if (!$fechaDesembolso) return;

        $fecha = Carbon::parse($fechaDesembolso)->addMonths(1);
        $set('fecha_primerpago', $fecha->toDateString());
    }

    public static function calcularFechaVencimiento(callable $get, callable $set)
    {
        if ($fecha = $get('fecha_desembolso')) {
            $plazo = intval($get('plazo'));
            $fecha = Carbon::parse($fecha)->addMonths($plazo);
            $set('fecha_vencimiento', $fecha->toDateString());
        }
    }

    public static function calcularDescuento(callable $get, callable $set)
    {
        $lineaCreditoId = $get('crelinea_id');
        $montoSolicitado = $get('monto_solicitado');
        if (!$lineaCreditoId || !$montoSolicitado) {
            $set('descuentos', 0);
            return;
        }
        $costos = Crelineacosto::where('crelinea_id', $lineaCreditoId)->where('aplicacion', 'desembolso')->first();

        if ($costos && $costos->es_porcentaje) {
            $set('descuentos', $montoSolicitado * ($costos->valor / 100));
        } else {
            $set('descuentos', $costos->valor ?? 0);
        }
    }

    public static function registrarMovimientoDesembolso($record, $data)
    {
        $cajaActiva = Caja::where('agencia_id', $record->agencia_id)->where('abierta', true)->first();
        $creditos = Credito::where('cliente_id', $record->cliente_id)->whereIn('estado', ['desembolsado', 'pagado', 'vencido'])->get();

        if ($cajaActiva) {
            $monto = floatval($data['monto_desembolsado']);
            $numeroRenovaciones = $creditos->count();
            $record->update([
                'crelinea_id' => $data['crelinea_id'],
                'destino_id' => $data['destino_id'],
                'monto_desembolsado' => $data['monto_desembolsado'],
                'descuentos' => $data['descuentos'],
                'interes_anual' => $data['interes_anual'],
                'plazo' => $data['plazo'],
                // 'tipo_plazo' => $data['tipo_plazo'],
                'tipo_cuota' => $data['tipo_cuota'],
                'cuota' => $data['cuota'],
                'fecha_desembolso' => $data['fecha_desembolso'],
                'fecha_primerpago' => $data['fecha_primerpago'],
                'fecha_vencimiento' => $data['fecha_vencimiento'],
                'notas' => $data['notas'],
                'estado' => 'desembolsado',
                'numero_renovaciones' => $numeroRenovaciones,
                'saldo_capital' => $data['monto_desembolsado'],
                'fecha_ultimopago' => $data['fecha_desembolso'],
            ]);
            Crmovimiento::create([
                'agencia_id'       => $record->agencia_id,
                'caja_id'          => $cajaActiva->id,
                'credito_id'        => $record->id,
                'fecha'            => $data['fecha_desembolso'],
                'comprobante'      => $data['comprobante'],
                'tipo'             => $data['tipo'],
                'desembolso'         => $data['monto_desembolsado'],
                'descuentos'            => $data['descuentos'],
                'saldocap'            => $data['monto_desembolsado'],
                'notas'            => 'Desembolso',
            ]);
            Notification::make()
                ->title('Desembolso Exitoso')
                ->body("Se ha realizado el desembolso de Q{$monto}, crédito: {$record->codigo}.")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('No hay caja abierta')
                ->warning()
                ->body("No hay una caja activa para {$record->agencia->nombre}.")
                ->send();
        }
    }

    public static function calcularpago($record)
    {
        $credito = Credito::find($record);
        // dump($credito);
        if ($credito) {

            // //MOSTRAR DATOS DEL CREDITO
            // $set('desembolsos', $credito->monto_desembolsado);
            // $set('entrega', $credito->fecha_desembolso);
            // $set('vencimiento', $credito->fecha_vencimiento);
            // $set('plazo', $credito->plazo);
            // $set('cuota', $credito->cuota);
            // $set('ultimopago', $credito->fecha_ultimopago);
            // $set('atraso', $credito->atraso);
            // $set('saldoCapital', $credito->saldo_capital);
            // $set('saldoInteres', $credito->saldo_interes);
            // $set('saldoMora', $credito->saldo_mora);
            // $set('ciclos', $credito->numero_renovaciones);

            $fechaDesembolso = Carbon::parse($credito->fecha_desembolso);
            $fecha_ultimo_pago = Carbon::parse($credito->fecha_ultimopago);
            $fechaActual = Carbon::parse(now());
            $plazo = $credito->plazo;
            $montoTotal = $credito->monto_desembolsado;
            $tasaInteresAnual = $credito->crelinea->tasa_interes;
            $tasaInteresMensual = $tasaInteresAnual / 12 / 100;
            $tasaInteresDiario = $tasaInteresAnual / 365 / 100;

            if ($credito->tipo_cuota == 'frances') {
                $fechaProximoVencimiento = Carbon::parse($credito->fecha_desembolso)->addMonths(floor(($fecha_ultimo_pago->diffInMonths(Carbon::parse($credito->fecha_desembolso), true)) + 1))->format('Y-m-d');
                $dias_transcurridos = intval($fecha_ultimo_pago->diffInDays($fechaActual));

                if ($fechaProximoVencimiento > $fechaActual) {
                    $diasAtraso = 0;
                } else {
                    $diasAtraso = intval($fechaActual->diffInDays($fechaProximoVencimiento, true));
                }

                $cuotaMensual = round(($montoTotal * $tasaInteresMensual) / (1 - pow(1 + $tasaInteresMensual, -$plazo)), 2);
                $meses_transcurridos = intval($fechaDesembolso->diffInMonths($fechaActual));
                $meses_pagados = intval($fechaDesembolso->diffInMonths($fecha_ultimo_pago));
                $saldo = $montoTotal;
                $capital_vencido = 0;
                $capital_acumulado = 0;
                $capital_mes = 0;

                $capitalpagado = Crmovimiento::where('credito_id', $credito->id)->whereNull('deleted_at')->sum('capital');

                for ($i = 1; $i <= $meses_transcurridos; $i++) {
                    $interes_mes = $saldo * $tasaInteresMensual;
                    $capital_mes = $cuotaMensual - $interes_mes;
                    $capital_acumulado += $capital_mes;
                    if ($i > $meses_pagados) {
                        $capital_vencido = $capital_acumulado - $capitalpagado;
                    }
                    $saldo -= $capital_mes;
                }
                $interes_apagar = $credito->saldo_capital * $tasaInteresDiario * $dias_transcurridos;
                $capital_vencido = max(0, $capital_vencido);
                $mora = 0;
                $pago_sugerido = $interes_apagar;
                if ($diasAtraso > 0) {
                    $tasaMoraDiario = $credito->crelinea->tasa_mora / 365 / 100;
                    $mora = ($capital_vencido * $tasaMoraDiario) * $diasAtraso;
                    $pago_sugerido = $capital_vencido + $interes_apagar + $mora;
                }
            }
            if ($credito->tipo_cuota == 'aleman') {
                $fechaProximoVencimiento = Carbon::parse($credito->fecha_desembolso)->addMonths(floor(($fecha_ultimo_pago->diffInMonths(Carbon::parse($credito->fecha_desembolso), true)) + 1))->format('Y-m-d');
                $dias_transcurridos = intval($fecha_ultimo_pago->diffInDays($fechaActual));

                if ($fechaProximoVencimiento > $fechaActual) {
                    $diasAtraso = 0;
                } else {
                    $diasAtraso = intval($fechaActual->diffInDays($fechaProximoVencimiento, true));
                }

                $meses_transcurridos = intval($fechaDesembolso->diffInMonths($fechaActual));
                $meses_pagados = intval($fechaDesembolso->diffInMonths($fecha_ultimo_pago));
                $saldo = $montoTotal;
                $capital_vencido = 0;
                $capital_acumulado = 0;
                $capital_mes = 0;

                $capitalpagado = Crmovimiento::where('credito_id', $credito->id)->whereNull('deleted_at')->sum('capital');

                for ($i = 1; $i <= $meses_transcurridos; $i++) {
                    $cuotaMensual = $saldo / ($plazo - ($i - 1)) + ($saldo * $tasaInteresMensual);
                    $interes_mes = $saldo * $tasaInteresMensual;
                    $capital_mes = $cuotaMensual - $interes_mes;
                    $capital_acumulado += $capital_mes;
                    if ($i > $meses_pagados) {
                        $capital_vencido = $capital_acumulado - $capitalpagado;
                    }
                    $saldo -= $capital_mes;
                }
                $interes_apagar = $credito->saldo_capital * $tasaInteresDiario * $dias_transcurridos;
                $capital_vencido = max(0, $capital_vencido);
                $mora = 0;
                $pago_sugerido = $interes_apagar;
                if ($diasAtraso > 0) {
                    $tasaMoraDiario = $credito->crelinea->tasa_mora / 365 / 100;
                    $mora = ($capital_vencido * $tasaMoraDiario) * $diasAtraso;
                    $pago_sugerido = $capital_vencido + $interes_apagar + $mora;
                }
            }
            if ($credito->tipo_cuota == 'americano') {
                $fechaProximoVencimiento = Carbon::parse($credito->fecha_vencimiento)->format('Y-m-d');
                $dias_transcurridos = intval($fecha_ultimo_pago->diffInDays($fechaActual));

                $saldo = $montoTotal;
                $capital_vencido = 0;
                $capital_acumulado = 0;
                $capital_mes = 0;
                $capitalpagado = Crmovimiento::where('credito_id', $credito->id)->whereNull('deleted_at')->sum('capital');
                if ($fechaProximoVencimiento > $fechaActual) {
                    $diasAtraso = 0;
                } else {
                    $capital_vencido = $montoTotal - $capitalpagado;
                    $diasAtraso = intval($fechaActual->diffInDays($fechaProximoVencimiento, true));
                }

                $interes_apagar = $credito->saldo_capital * $tasaInteresDiario * $dias_transcurridos;
                $capital_vencido = max(0, $capital_vencido);
                $mora = 0;
                $pago_sugerido = $interes_apagar;
                if ($diasAtraso > 0) {
                    $tasaMoraDiario = $credito->crelinea->tasa_mora / 365 / 100;
                    $mora = ($capital_vencido * $tasaMoraDiario) * $diasAtraso;
                    $pago_sugerido = $capital_vencido + $interes_apagar + $mora;
                }
            }
            if ($credito->tipo_cuota == 'flat') {
                $fechaProximoVencimiento = Carbon::parse($credito->fecha_desembolso)->addMonths(floor(($fecha_ultimo_pago->diffInMonths(Carbon::parse($credito->fecha_desembolso), true)) + 1))->format('Y-m-d');
                $dias_transcurridos = intval($fecha_ultimo_pago->diffInDays($fechaActual));

                if ($fechaProximoVencimiento > $fechaActual) {
                    $diasAtraso = 0;
                } else {
                    $diasAtraso = intval($fechaActual->diffInDays($fechaProximoVencimiento, true));
                }

                $cuotaMensual = round(($montoTotal + ($montoTotal * (($tasaInteresAnual / 100) / 12) * $plazo)) / $plazo, 2);

                $meses_transcurridos = intval($fechaDesembolso->diffInMonths($fechaActual));
                $meses_pagados = intval($fechaDesembolso->diffInMonths($fecha_ultimo_pago));
                $saldo = $montoTotal;
                $capital_vencido = 0;
                $capital_acumulado = 0;
                $capital_mes = 0;

                $capitalpagado = Crmovimiento::where('credito_id', $credito->id)->whereNull('deleted_at')->sum('capital');

                for ($i = 1; $i <= $meses_transcurridos; $i++) {
                    $interes_mes = $montoTotal * $tasaInteresMensual;
                    $capital_mes = $cuotaMensual - ($montoTotal * $tasaInteresMensual);
                    $capital_acumulado += $capital_mes;
                    if ($i > $meses_pagados) {
                        $capital_vencido = $capital_acumulado - $capitalpagado;
                    }
                    $saldo -= $capital_mes;
                }
                $interes_apagar = $montoTotal * $tasaInteresMensual;
                $capital_vencido = max(0, $capital_vencido);
                $mora = 0;
                $pago_sugerido = $interes_apagar;
                if ($diasAtraso > 0) {
                    $tasaMoraDiario = $credito->crelinea->tasa_mora / 365 / 100;
                    $mora = ($capital_vencido * $tasaMoraDiario) * $diasAtraso;
                    $pago_sugerido = $capital_vencido + $interes_apagar + $mora;
                }
            }

            $calculo[] = [
                'capital' => number_format($capital_vencido, 2, '.', ''),
                'interes' => number_format($interes_apagar, 2, '.', ''),
                'mora' => number_format($mora, 2, '.', ''),
                'atraso' => $diasAtraso,
                'total' => number_format($credito->saldo_capital + $interes_apagar + $mora, 2, '.', ''),
                'pago' => number_format($pago_sugerido, 2, '.', ''),
            ];
            return $calculo;
        }
    }
}
