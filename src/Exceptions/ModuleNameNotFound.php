<?php

namespace JackJack\Modules\Exceptions;

class ModuleNameNotFound extends \Exception
{
    public static function make(): self
    {
        return new static("Module not determined. The path to the file must contain Paamayim Nekudotaim");
    }
}
