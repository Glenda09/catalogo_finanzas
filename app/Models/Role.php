<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function users()
    {
        return $this->belongsToMany(Usuarios::class, 'usuario_rol', 'id_rol', 'id_usuario');
    }
}
