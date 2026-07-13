<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasSequentialCode;
use Illuminate\Database\Eloquent\Collection;
use MongoDB\Laravel\Eloquent\Model;

class Perfil extends Model
{
    use Auditable;
    use HasSequentialCode;

    protected static string $codigoPrefix = 'PRF';

    protected $connection = 'mongodb';

    protected $collection = 'perfiles';

    protected $fillable = [
        'codigo',
        'nombre',
        'seccion_ids',
    ];

    public function secciones(): Collection
    {
        return Seccion::whereIn('_id', $this->seccion_ids ?? [])->get();
    }
}
