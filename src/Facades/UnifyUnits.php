<?php

namespace UnifyUnits\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class UnifyUnits extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'unifyunits';
    }
}
