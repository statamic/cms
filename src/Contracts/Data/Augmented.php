<?php

namespace Statamic\Contracts\Data;

interface Augmented
{
    public function get($key);

    public function all();

    public function select($keys = null);
}
