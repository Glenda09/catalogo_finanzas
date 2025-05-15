<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;
    protected $table = 'modulo';

    protected $fillable = [
        'id_curso',
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }
}
