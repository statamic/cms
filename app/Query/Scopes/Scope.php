<?php

namespace Statamic\Query\Scopes;

use Statamic\Extend\HasHandle;
use Statamic\Extend\RegistersItself;

abstract class Scope
{
    use RegistersItself, HasHandle;

    protected static $binding = 'scopes';
}
