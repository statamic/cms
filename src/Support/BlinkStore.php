<?php

namespace Statamic\Support;

use Spatie\Blink\Blink as SpatieBlink;

class BlinkStore extends SpatieBlink
{
    private $wildcards = false;

    public function withWildcards()
    {
        $this->wildcards = true;
    }

    protected function stringContainsWildcard(string $key): bool
    {
        return $this->wildcards && parent::stringContainsWildcard($key);
    }
}
