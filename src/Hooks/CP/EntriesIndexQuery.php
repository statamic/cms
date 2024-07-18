<?php

namespace Statamic\Hooks\CP;

use Statamic\Support\Traits\Hookable;

class EntriesIndexQuery
{
    use Hookable;

    public function __construct(private $query)
    {
        //
    }

    public function paginate(int $perPage)
    {
        $query = $this->runHooks('query', $this->query);

        return $query->paginate($perPage);
    }
}
