<?php

namespace Statamic\Modifiers;

use Statamic\Extend\HasAliases;
use Statamic\Extend\HasHandle;
use Statamic\Extend\RegistersItself;

/**
 * Modify values within templates.
 */
class Modifier
{
    use HasHandle, RegistersItself, HasAliases;
}
