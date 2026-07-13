<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use MongoDB\Operation\FindOneAndUpdate;

class SequenceGenerator
{
    /**
     * Atomically increments and returns the next number for the given sequence key,
     * using a single MongoDB findOneAndUpdate so concurrent creates never collide.
     */
    public static function next(string $key): int
    {
        $result = DB::connection('mongodb')->getCollection('counters')->findOneAndUpdate(
            ['_id' => $key],
            ['$inc' => ['seq' => 1]],
            ['upsert' => true, 'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER],
        );

        return $result->seq;
    }
}
