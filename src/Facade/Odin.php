<?php

namespace ctf0\Odin\Facade;

use Illuminate\Support\Facades\Facade;

class Odin extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'odin';
    }
}
