<?php

namespace Statamic\Testing\Doubles;

use Statamic\API\Entries;
use Statamic\Stache\Manager;

class StacheTestManager extends Manager
{
    public function load()
    {
        // Prevent the repos trying to load from the filesystem when requested.
        $this->stache->repos()->each(function ($repo) {
            $repo->loaded = true;
        });
    }

    public function update() { }
}
