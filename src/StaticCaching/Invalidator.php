<?php

namespace Statamic\StaticCaching;

interface Invalidator
{
    public function invalidate($item);
}
