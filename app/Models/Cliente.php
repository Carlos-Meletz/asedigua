<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Cliente extends Model
{
    protected $fillable = [
        'nombre',
        'apellido',
        'fecha_nacimiento',
        'edad',
        'genero',
        'dpi',
        'dpi_dep',
        'dpi_mun',
        'estado_civil',
        'estado',
        'fotografia',
        //contacto
        'telefono',
        'celular',
        'correo',
        'social',
        'archivos',
        //direccion
        'departamento',
        'municipio',
        'direccion',
        'latitude',
        'longitude',
        'notas',
    ];

    protected $casts = [
        'archivos' => 'json'
    ];

    // public function cuenta()
    // {
    //     return $this->hasMany(Cuenta::class);
    // }
    public function viviendas(): HasMany
    {
        return $this->hasMany(Vivienda::class);
    }

    public function trabajos(): HasMany
    {
        return $this->hasMany(Trabajo::class);
    }

    // public function finanzas()
    // {
    //     return $this->hasMany(Finanza::class);
    // }
    public function empleado()
    {
        return $this->hasOne(Empleado::class);
    }

    public function referencias(): HasMany
    {
        return $this->hasMany(Referencia::class);
    }



    protected static function booted(): void
    {
        //Eliminar archivo luego de eliminar el registro
        static::deleted(function (Cliente $cliente) {

            // Elimina la fotografía
            if ($cliente->fotografia) {
                Storage::disk('cliente')->delete($cliente->fotografia);
            }

            // Elimina múltiples archivos (si aplica)
            if (!empty($cliente->archivos)) {
                foreach ($cliente->archivos as $archivo) {
                    Storage::disk('archivo')->delete($archivo);
                }
            }
        });

        // Evento al actualizar un cliente
        static::updating(function (Cliente $cliente) {
            // Elimina fotografía si se actualiza
            if ($cliente->isDirty('fotografia')) {
                $originalFotografia = $cliente->getOriginal('fotografia');
                Storage::disk('cliente')->delete($originalFotografia);
            }

            // Elimina archivos múltiples si se actualizan

            // Detecta los archivos eliminados
            $archivosEliminados = array_diff($cliente->getOriginal('archivos'), $cliente->archivos);

            // Elimina solo los archivos eliminados
            foreach ($archivosEliminados as $archivo) {
                Storage::disk('archivo')->delete($archivo);
            }
        });
    }
}
