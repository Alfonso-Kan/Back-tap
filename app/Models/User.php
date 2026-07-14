<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasSequentialCode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Auditable;
    use HasApiTokens;
    use HasSequentialCode;
    use Notifiable;

    protected static string $codigoPrefix = 'USR';

    protected $connection = 'mongodb';

    protected $table = 'users';

    protected $fillable = [
        'codigo',
        'nombre',
        'usuario',
        'password',
        'telefono',
        'foto_perfil',
        'perfil_ids',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function perfiles(): Collection
    {
        return Perfil::whereIn('_id', $this->perfil_ids ?? [])->get();
    }

    public function seccionesAccesibles(): Collection
    {
        $seccionIds = $this->perfiles()
            ->pluck('seccion_ids')
            ->flatten()
            ->unique()
            ->values()
            ->all();

        return Seccion::whereIn('_id', $seccionIds)->get();
    }

    public function tieneAccesoASeccion(string $codigo): bool
    {
        return $this->seccionesAccesibles()->contains('codigo', $codigo);
    }
}
