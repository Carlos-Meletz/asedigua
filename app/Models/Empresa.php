<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $fillable = [
        'nombre',
        'razon_social',
        'nit',
        'tipo_empresa',
        'fecha_constitucion',
        'direccion_fiscal',
        'rps_nombre',
        'rps_dpi',
        'rps_dpiDep',
        'rps_dpiMun',
        'rps_cargo',
        'rps_profesion',
        'rps_fechaNac',
        'rps_edad',
        'rps_estado_civil',
        'rps_direccion',
        'logo',
        'rps_telefono',
    ];
}
