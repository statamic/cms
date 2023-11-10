<?php

namespace Statamic\Query\Scopes\Filters\Fields;

class Integer extends Number
{
    protected function valueFieldtype()
    {
        return 'integer';
    }
}
