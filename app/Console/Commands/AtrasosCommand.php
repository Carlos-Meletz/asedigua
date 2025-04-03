<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Credito;
use Illuminate\Console\Command;

class AtrasosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:atrasos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculo de dias de atraso de Créditos';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        Credito::where('saldo_capital', '>', 0)->chunk(100, function ($creditos) {
            foreach ($creditos as $credito) {
                $fecha_ultimo_pago = $credito->fecha_ultimopago ? Carbon::parse($credito->fecha_ultimopago) : Carbon::parse($credito->fecha_desembolso);
                $fechaActual = Carbon::now();

                if ($fechaActual > $credito->fecha_vencimiento) {
                    $estado = 'vencido';
                    // $credito->update(['estado' => 'vencido']);
                }
                if ($credito->tipo_cuota == 'americano') {
                    $fechaProximoVencimiento = Carbon::parse($credito->fecha_vencimiento);
                } else {
                    $fechaProximoVencimiento = Carbon::parse($credito->fecha_desembolso)->addMonths(floor(($fecha_ultimo_pago->diffInMonths(Carbon::parse($credito->fecha_desembolso), true)) + 1))->format('Y-m-d');
                }
                if ($fechaProximoVencimiento > $fechaActual) {
                    $diasAtraso = 0;
                    $estado = 'desembolsado';
                    // $credito->update(['estado' => 'desembolsado']);
                } else {
                    $diasAtraso = intval($fechaActual->diffInDays($fechaProximoVencimiento, true));
                    $estado = 'atrasado';
                    // $credito->update(['estado' => 'atrasado']);
                }
                $credito->update([
                    'estado' => $estado,
                    'dias_atraso' => $diasAtraso,
                ]);
            }
        });

        $this->info('Días de atraso actualizados correctamente para los créditos activos.');
    }
}
