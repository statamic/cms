<?php

namespace Tests\Antlers\Fixtures\Addon\Modifiers;

use Mockery\MockInterface;
use Statamic\Modifiers\Modifier;
use Statamic\Support\Str;

class IsBuilder extends Modifier
{
    protected static $handle = 'is_builder';

    public function index($value)
    {
        if ($value instanceof MockInterface) {
            $className = get_class($value);

            if (Str::endsWith($className, 'Statamic_Contracts_Query_Builder')) {
                return 'Statamic\Contracts\Query\Builder';
            }
        }

        return 'Definitely\Did\Not\Get\The\Mocked\Builder';
    }
}
