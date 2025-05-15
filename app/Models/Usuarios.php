<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuarios extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'password',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'usuario_rol', 'id_usuario', 'id_rol');
    }
}
