<?php

namespace Statamic\Tasks;

use Spatie\Fork\Fork;
use Statamic\Facades\Config;

class ConcurrentTasks implements Tasks
{
    protected $fork;
    protected $concurrent;

    public function __construct(Fork $fork)
    {
        $this->fork = $fork;
        $this->concurrent = Config::get('statamic.system.max_concurrent_tasks', null);
    }

    public function run(...$closures)
    {
        if ($this->concurrent !== null) {
            $this->fork->concurrent($this->concurrent);
        }

        return $this->fork
            ->run(...$closures);
    }
}
