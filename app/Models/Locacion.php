<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Locacion extends Model
{
    protected $fillable = ['type', 'name', 'parent_id'];

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public static function departamentos()
    {
        return self::where('type', 'Departamento')->pluck('name', 'id');
    }

    public static function municipios($departamentoId)
    {
        return self::where('parent_id', $departamentoId)->pluck('name', 'id');
    }
}
