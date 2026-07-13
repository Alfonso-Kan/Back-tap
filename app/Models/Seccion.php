<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Seccion extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'secciones';

    protected $fillable = [
        'codigo',
        'nombre',
    ];
}
