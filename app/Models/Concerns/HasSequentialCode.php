<?php

namespace App\Models\Concerns;

use App\Support\SequenceGenerator;

/**
 * Assigns an auto-generated, human-readable code (e.g. PRD-000001) on creation.
 * The model must define protected static string $codigoPrefix.
 */
trait HasSequentialCode
{
    public static function bootHasSequentialCode(): void
    {
        static::creating(function ($model) {
            if (empty($model->codigo)) {
                $model->codigo = sprintf(
                    '%s-%06d',
                    static::$codigoPrefix,
                    SequenceGenerator::next(static::$codigoPrefix),
                );
            }
        });
    }
}
