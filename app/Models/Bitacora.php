<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Bitacora extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'bitacoras';

    public $timestamps = false;

    protected $fillable = [
        'coleccion',
        'documento_id',
        'accion',
        'datos_anteriores',
        'datos_nuevos',
        'usuario_id',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function usuario()
    {
        return $this->usuario_id ? User::find($this->usuario_id) : null;
    }
}
