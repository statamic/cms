<?php

namespace Statamic\Events\Stache;

use Statamic\Events\Event;

class RepositoryItemRemoved extends Event
{
    public $id;
    public $item;
    private $repo;

    public function __construct($repo, $id, $item)
    {
        $this->id = $id;
        $this->item = $item;
        $this->repo = $repo;
    }
}