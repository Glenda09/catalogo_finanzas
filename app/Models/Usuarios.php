<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Usuarios extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'ultima_sesion',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'usuario_rol', 'id_usuario', 'id_rol');
    }

    // Métodos requeridos por JWTSubject:
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Mutator para hashear password automáticamente
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    // Método para verificar rol
    public function tieneRol($nombreRol)
    {
        return $this->roles()->where('nombre', $nombreRol)->exists();
    }

    public function instructor()
    {
        return $this->hasMany(Instructor::class, 'id_usuario', 'id');
    }
}
