<?php

namespace Statamic\Events\Stache;

use Statamic\Events\Event;

class RepositoryItemInserted extends Event
{
    public $id;
    public $item;
    public $repo;

    public function __construct($repo, $id, $item)
    {
        $this->id = $id;
        $this->item = $item;
        $this->repo = $repo;
    }
}