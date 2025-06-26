<?php

namespace Statamic\Hooks\CP;

use Statamic\Support\Traits\Hookable;

class Blueprint
{
    use Hookable;

    public function makeFromFile($path, $namespace)
    {
        $payload = $this->runHooksWith('makeFromFile', [
            'namespace' => $namespace,
            'path' => $path,
        ]);

        return $payload;
    }
}
