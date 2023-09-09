<?php

namespace ItsRafsanJani\Bkash\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ItsRafsanJani\Bkash\Bkash
 */
class Bkash extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \ItsRafsanJani\Bkash\Bkash::class;
    }
}
