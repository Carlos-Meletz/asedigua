<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Cliente;
use Illuminate\Console\Command;

class ActualizarEdadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:uptage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizacion de Edades de Clientes registrados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clientes = Cliente::all();

        foreach ($clientes as $cliente) {
            // Calcular la edad
            $edad = Carbon::parse($cliente->fecha_nacimiento)->age;
            if ($cliente->fecha_nacimiento != $edad) {
                // Actualizar el campo "edad" en la base de datos
                $cliente->update(['edad' => $edad]);
            }
        }

        $this->info('Las edades de los clientes han sido actualizadas correctamente.');
    }
}
