<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use MongoDB\Laravel\Eloquent\Model;

class Seccion extends Model
{
    use Auditable;

    protected $connection = 'mongodb';

    protected $table = 'secciones';

    protected $fillable = [
        'codigo',
        'nombre',
    ];
}
