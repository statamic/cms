<?php

namespace Statamic\Data\Services;

use Statamic\Stache\Stache;

abstract class AbstractService
{
    /**
     * @var \Statamic\Stache\Stache
     */
    protected $stache;

    /**
     * @var string
     */
    protected $repo;

    /**
     * @param \Statamic\Stache\Stache $stache
     */
    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
    }

    /**
     * @return \Statamic\Stache\Repository|\Statamic\Stache\AggregateRepository
     */
    protected function repo()
    {
        return $this->stache->repo($this->repo);
    }
}
