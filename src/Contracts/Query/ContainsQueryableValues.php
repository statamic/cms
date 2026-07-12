<?php

namespace Statamic\Contracts\Query;

interface ContainsQueryableValues
{
    public function getQueryableValue(string $field);
}
