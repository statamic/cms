<?php

namespace Statamic\StaticCaching;

interface Invalidator
{
    public function invalidate($item);

    public function refresh($item);
}
