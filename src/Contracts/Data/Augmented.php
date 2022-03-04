<?php

namespace Statamic\Contracts\Data;

use Statamic\Fields\Value;

interface Augmented
{
    public function get($key): Value;

    public function all();

    public function select($keys = null);

    public function withRelations($relations);
}
