<?php

namespace Statamic\Hooks\CP;

use Statamic\Assets\AssetContainer;
use Statamic\Assets\QueryBuilder;
use Statamic\Support\Traits\Hookable;

class AssetsIndexQuery
{
    use Hookable;

    public function __construct(private QueryBuilder $query, private AssetContainer $container)
    {
        //
    }

    public function query(): QueryBuilder
    {
        $payload = $this->runHooksWith('query', [
            'query' => $this->query,
            'container' => $this->container,
        ]);

        return $payload->query;
    }
}
