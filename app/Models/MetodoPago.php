<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    use HasFactory;
    protected $table = 'metodo_pago';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    public function inscripcion()
    {
        return $this->hasMany(Inscripcion::class, 'id_metodo_pago');
    }
}
