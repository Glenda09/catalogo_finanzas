<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    use HasFactory;
    protected $table = 'instructors';

    protected $fillable = [
        'id_usuario',
        'activo',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuarios::class, 'id_usuario');
    }
}
