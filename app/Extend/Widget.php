<?php

namespace Statamic\Extend;

use Statamic\API\Str;
use Statamic\Extend\HasTitleAndHandle;

abstract class Widget
{
    use HasParameters, HasTitleAndHandle {
        handle as protected traitHandle;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    public static function handle()
    {
        return Str::removeRight(static::traitHandle(), '_widget');
    }
}
