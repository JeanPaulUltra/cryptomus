<?php

namespace Kristof\Cryptomus\Facades;
use Illuminate\Support\Facades\Facade;
class Cryptomus extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cryptomus';
    }
}
