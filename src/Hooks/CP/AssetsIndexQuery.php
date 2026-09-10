<?php

namespace Statamic\Hooks\CP;

use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Support\Traits\Hookable;

class AssetsIndexQuery
{
    use Hookable;

    public function __construct(private $query, private AssetContainer $container)
    {
        //
    }

    public function query()
    {
        $payload = $this->runHooksWith('query', [
            'query' => $this->query,
            'container' => $this->container,
        ]);

        return $payload->query;
    }
}
