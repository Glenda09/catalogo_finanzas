<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    use HasFactory;
    protected $table = 'inscripcions';
    protected $fillable = [
        'id_usuario',
        'id_curso',
        'fecha_inscripcion',
        'activo',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuarios::class, 'id_usuario');
    }
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }
}
