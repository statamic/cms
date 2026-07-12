<?php

namespace Statamic\Hooks\CP;

use Statamic\Support\Traits\Hookable;

class EntriesIndexQuery
{
    use Hookable;

    public function __construct(private $query, private $collection)
    {
        //
    }

    public function paginate(?int $perPage)
    {
        $payload = $this->runHooksWith('query', [
            'query' => $this->query,
            'collection' => $this->collection,
        ]);

        return $payload->query->paginate($perPage);
    }
}
