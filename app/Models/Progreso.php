<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progreso extends Model
{
    use HasFactory;
    protected $table = 'progresos';

    protected $fillable = [
        'id_inscripcion',
        'id_modulo',
        'fecha_inicio',
        'completado',
        'calificacion',
        'fecha_fin',
        'tiempo_total',
        'activo',
    ];

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion');
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo');
    }
}
