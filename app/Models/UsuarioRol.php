<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioRol extends Model
{
    use HasFactory;

    protected $table = 'usuario_rol';
    protected $fillable = [
        'id_usuario',
        'id_rol',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuarios::class, 'id_usuario');
    }
    public function rol()
    {
        return $this->belongsTo(Role::class, 'id_rol');
    }
}
