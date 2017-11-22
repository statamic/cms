<?php

namespace Statamic\Events;

use Statamic\Stache\Stache;
use Illuminate\Support\Collection;

class StacheUpdated extends Event
{
    /**
     * The Stache
     *
     * @var Stache
     */
    public $stache;

    /**
     * The Stache repos that have been updated
     *
     * @var Collection
     */
    public $updates;

    /**
     * Create a new event instance
     *
     * @param Collection $updates
     * @param Stache     $stache
     */
    public function __construct(Collection $updates, Stache $stache)
    {
        $this->updates = $updates;
        $this->stache = $stache;
    }

    /**
     * Determine if a given repo was updated
     *
     * @param string $repo
     * @return bool
     */
    public function updated($repo)
    {
        return $this->updates->contains($repo);
    }

    /**
     * Determine if any given repos were updated
     *
     * @param array $repos
     * @return bool
     */
    public function updatedAny(array $repos)
    {
        foreach ($repos as $repo) {
            if ($this->updated($repo)) {
                return true;
            }
        }

        return false;
    }
}
