<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(GuatemalaSeeder::class);

        User::factory()->create([
            'name' => 'Carlos Meletz Chiyal',
            'username' => 'cmc',
            'email' => 'admin@simec.com',
            'email_verified_at' => NULL,
            'password' => '$2y$12$5vqnMrl32mGsvmQeddrkq.irZ0KjCxlCkQhPTw4vcg3xUPXZvVI/.',
            'remember_token' => NULL,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        User::factory()->create([
            'name' => 'Steven Stuart',
            'username' => 'stvSt',
            'email' => 'user@simec.com',
            'email_verified_at' => NULL,
            'password' => '$2y$12$979sNzerC6Wp2yYRyxevWu1rY7aSRTd64zZEi/hHWA/zkXAg6yati',
            'remember_token' => NULL,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('agencias')->insert([
            'nombre' => 'Central Sololá',
            'codigo' => '001',
            'departamento' => '277',
            'municipio' => '278',
            'direccion' => '15 Avenida, Barrio el Carmen',
            'latitude' => 14.76906722,
            'longitude' => -91.18349791,
            'telefono' => '77623238',
            'email' => 'central@simec.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('agencias')->insert([
            'nombre' => 'Xajaxac',
            'codigo' => '002',
            'departamento' => '277',
            'municipio' => '278',
            'direccion' => 'Xajaxac, Sololá',
            'latitude' => 14.76906722,
            'longitude' => -91.18349791,
            'telefono' => '77623238',
            'email' => 'xajaxac@simec.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('crelineas')->insert([
            'nombre' => 'MICROEMPRESA 24%',
            'tasa_interes' => 24.00,
            'tasa_mora' => 1.00,
            'plazo_min' => 3,
            'plazo_max' => 36,
            'monto_min' => 500.00,
            'monto_max' => 50000.00,
            'activo' => 1,
            'condiciones' => 'Préstamos para microempresas',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('crelineas')->insert([
            'nombre' => 'AGRICOLA 12%',
            'tasa_interes' => 12.00,
            'tasa_mora' => 1.00,
            'plazo_min' => 1,
            'plazo_max' => 6,
            'monto_min' => 500.00,
            'monto_max' => 25000.00,
            'activo' => 0,
            'condiciones' => 'Prestamos para inversión en agricultura y exportación, con disposición de pago de capital al vencimiento.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('crelineacostos')->insert([
            'crelinea_id' => 1,
            'tipo' => 'costo_administrativo',
            'es_porcentaje' => 1,
            'valor' => 5.00,
            'aplicacion' => 'desembolso',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('crelineacostos')->insert([
            'crelinea_id' => 1,
            'tipo' => 'microseguro',
            'es_porcentaje' => 0,
            'valor' => 150.00,
            'aplicacion' => 'cuotas',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('crelineacostos')->insert([
            'crelinea_id' => 2,
            'tipo' => 'costo_administrativo',
            'es_porcentaje' => 1,
            'valor' => 3.00,
            'aplicacion' => 'desembolso',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('aholineas')->insert([
            'nombre' => 'PROMESA 10%',
            'tasa_interes' => 10.00,
            'tasa_interes_minima' => 3.00,
            'tasa_penalizacion' => 4.00,
            'plazo_minimo' => 6,
            'plazo_maximo' => 24,
            'monto_min' => 80000.00,
            'monto_max' => 200000.00,
            'activo' => 1,
            'condiciones' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('clientes')->insert([
            'nombre' => 'Francisco Alejandro',
            'apellido' => 'Sicajau Yaxon',
            'fecha_nacimiento' => '2001-02-28',
            'edad' => 24,
            'genero' => 'masculino',
            'dpi' => '3108 91183 0701',
            'dpi_dep' => '277',
            'dpi_mun' => '277',
            'estado_civil' => 'soltero',
            'estado' => 'activo',
            'fotografia' => '01JKS0KHMPC7AXSBF4JQJ4YC62.jpg',
            'telefono' => '+1602498-5316',
            'celular' => '+1681929-5543',
            'correo' => 'sysyx@mailinator.com',
            'social' => 0,
            'archivos' => '["01JKS0KHNKREA4XK4PNED21FMQ.jpg", "01JKS0KHNRHJ8HN478PNTQMHEP.jpg", "01JKS0KHNT1NSZMBVPEQSC66TJ.jpg", "01JKS0KHNYNFQ7B9W62P64923V.jpg"]',
            'departamento' => '277',
            'municipio' => '278',
            'direccion' => 'Sit omnis quis labor',
            'latitude' => 14.76860239,
            'longitude' => -91.18602991,
            'notas' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('fondos')->insert([
            'nombre' => 'Fondos de Ahorrantes General',
            'tipo' => 'ahorro',
            'balance' => 0.00,
            'descripcion' => NULL,
            'activo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('fondos')->insert([
            'nombre' => 'Fondo de Créditos Microempresa',
            'tipo' => 'credito',
            'balance' => 0.00,
            'descripcion' => NULL,
            'activo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('fondos')->insert([
            'nombre' => 'Fondo Ahorro Infantil',
            'tipo' => 'ahorro',
            'balance' => 0.00,
            'descripcion' => NULL,
            'activo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('fondos')->insert([
            'nombre' => 'ASEDIGUA',
            'razon_social' => NULL,
            'nit' => '10836066',
            'tipo_empresa' => 'S.R.L.',
            'fecha_constitucion' => '2025-03-12',
            'direccion_fiscal' => 'Xajaxac',
            'rps_nombre' => 'Jose Lopez',
            'rps_dpi' => '3108911830701',
            'rps_dpiDep' => '77',
            'rps_dpiMun' => '55',
            'rps_cargo' => 'Gerente',
            'rps_profesion' => 'Licenciado',
            'rps_fechaNac' => '2025-03-12',
            'rps_edad' => 25,
            'rps_estado_civil' => 'Soltero',
            'rps_direccion' => 'Solola',
            'logo' => '01JP6VPAQX8D5F6T8MQNEFRVDN.png',
            'rps_telefono' => NULL,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
