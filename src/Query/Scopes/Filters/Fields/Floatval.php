<?php

namespace Statamic\Query\Scopes\Filters\Fields;

class Floatval extends Number
{
    protected function valueFieldtype()
    {
        return 'float';
    }
}
