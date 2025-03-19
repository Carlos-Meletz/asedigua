<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Garantia extends Model
{
    protected $fillable = [
        'credito_id',
        'tipo_garantia',
        'Descriptor',
        'valor_estimado',
        'descripcion',
        'observaciones',
        'numero_documento',
        'latitude',
        'longitude',
        'ubicacion',
        'superficie',
        'registro_propiedad',
        'nombre_propietario',
        'documentos',
        'notario_responsable',
        'fecha_registro',
    ];
    protected $casts = [
        'documentos' => 'json'
    ];
    public function Credito()
    {
        return $this->belongsTo(Credito::class);
    }

    protected static function booted(): void
    {
        //Eliminar archivo luego de eliminar el registro
        static::creating(function (Garantia $garantia) {
            if (!empty($garantia->documentos) && is_array($garantia->documentos)) {
                $newFileNames = [];

                foreach ($garantia->documentos as $filePath) {
                    // Obtener la extensión del archivo
                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);

                    // Generar un nuevo nombre único
                    $newFileName = Str::uuid() . '.' . $extension;

                    // Renombrar el archivo en el almacenamiento
                    if (Storage::disk('documento')->exists($filePath)) {
                        $newFileName = Str::uuid() . '-' . rand(1, 999) . '.' . $extension;
                        Storage::disk('documento')->copy($filePath, $newFileName);
                        // Storage::disk('documento')->move($filePath, $newFileName);
                    }

                    // Guardar el nuevo nombre en el array
                    $newFileNames[] = $newFileName;
                }

                // Actualizar el atributo con los nuevos nombres
                $garantia->documentos = $newFileNames;
            }
        });

        static::deleted(function (Garantia $garantia) {
            // Elimina múltiples archivos (si aplica)
            if (!empty($garantia->documentos)) {
                foreach ($garantia->documentos as $archivo) {
                    Storage::disk('documento')->delete($archivo);
                }
            }
        });

        // Evento al actualizar un cliente
        static::updating(function (Garantia $garantia) {
            // Detecta los archivos eliminados
            $archivosEliminados = array_diff($garantia->getOriginal('documentos'), $garantia->documentos);

            // Elimina solo los archivos eliminados
            foreach ($archivosEliminados as $archivo) {
                Storage::disk('documento')->delete($archivo);
            }
        });
    }
}
