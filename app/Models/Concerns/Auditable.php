<?php

namespace App\Models\Concerns;

use App\Models\Bitacora;
use Illuminate\Support\Facades\Auth;

/**
 * Writes a Bitacora entry (before/after snapshot) on create/update/delete,
 * so past data can be compared against current data per the spec's audit requirement.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->registrarBitacora('creacion', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            // getOriginal() still holds the pre-update values here: the "updated"
            // event fires before Eloquent syncs the original attributes.
            $model->registrarBitacora('actualizacion', $model->getOriginal(), $model->getAttributes());
        });

        static::deleted(function ($model) {
            $model->registrarBitacora('eliminacion', $model->getOriginal(), null);
        });
    }

    protected function registrarBitacora(string $accion, ?array $anterior, ?array $nuevo): void
    {
        Bitacora::create([
            'coleccion' => $this->getTable(),
            'documento_id' => (string) $this->getKey(),
            'accion' => $accion,
            'datos_anteriores' => $this->limpiarSnapshot($anterior),
            'datos_nuevos' => $this->limpiarSnapshot($nuevo),
            'usuario_id' => Auth::id() ? (string) Auth::id() : null,
            'fecha' => now(),
        ]);
    }

    protected function limpiarSnapshot(?array $attributes): ?array
    {
        if ($attributes === null) {
            return null;
        }

        // The id is already stored as documento_id; dropping it here also avoids an
        // inconsistent BSON ObjectId vs. string representation between create and update snapshots.
        return collect($attributes)->except([...$this->getHidden(), 'id', '_id'])->toArray();
    }
}
