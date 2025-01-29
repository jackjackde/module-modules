<?php

namespace JackJack\Modules\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JackJack\Modules\Modules
 */
class Modules extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'modules';
    }
}
