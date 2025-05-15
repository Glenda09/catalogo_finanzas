<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioSesion extends Model
{
    use HasFactory;
    protected $table = 'usuario_sesions';

    protected $fillable = [
        'id_usuario',
        'token',
        'expires_at',
        'closed_at',
        'updated_at',
        'created_at',
        'activo'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuarios::class, 'id_usuario');
    }
}
