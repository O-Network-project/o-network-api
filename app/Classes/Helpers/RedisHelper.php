<?php

namespace App\Classes\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

class RedisHelper
{
    /**
     * Return the expiration date of a key as a Carbon instance. Return null if
     * the key doesn't exist or doesn't expire.
     */
    public static function getKeyExpirationDate(string $key): ?Carbon
    {
        /** @var int $pttl */
        $pttl = Redis::pttl($key);

        // The PTTL command of Redis returns -1 if the key doesn't expire and -2
        // if it doesn't exist
        if ($pttl < 0) {
            return null;
        }

        return Carbon::now()->addMilliseconds($pttl);
    }
}
