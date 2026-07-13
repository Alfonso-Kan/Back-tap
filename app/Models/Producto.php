<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasSequentialCode;
use MongoDB\Laravel\Eloquent\Model;

class Producto extends Model
{
    use Auditable;
    use HasSequentialCode;

    protected static string $codigoPrefix = 'PRD';

    protected $connection = 'mongodb';

    protected $collection = 'productos';

    protected $fillable = [
        'codigo',
        'nombre',
        'marca',
        'precio',
    ];

    protected $casts = [
        'precio' => 'float',
    ];
}
