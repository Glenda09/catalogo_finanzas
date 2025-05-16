<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;
    protected $table = 'cursos';
    protected $fillable = [
        'titulo',
        'descripcion',
        'precio',
        'duracion_horas',
        'id_categoria',
        'id_instructor',
        'activo',
        'creado_por',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'id_instructor');
    }

    public function creadoPor()
    {
        return $this->belongsTo(Usuarios::class, 'creado_por');
    }
}
