<?php

namespace Statamic\Structures;

use Statamic\Support\Str;

class BranchIds
{
    protected $generator;

    public function __construct()
    {
        $this->generator = function () {
            return (string) Str::uuid();
        };
    }

    public function setIdGenerator(callable $generator)
    {
        $this->generator = $generator;

        return $this;
    }

    public function ensure(array $tree)
    {
        return collect($tree)->map(function ($branch) {
            return $this->ensureIdOnBranch($branch);
        })->all();
    }

    private function ensureIdOnBranch($branch)
    {
        $id = $branch['id'] ?? ($this->generator)();

        if ($branch['children'] ?? false) {
            $branch['children'] = $this->ensure($branch['children']);
        }

        return array_merge(['id' => $id], $branch);
    }
}
