<?php

namespace Statamic\Structures;

use Facades\Statamic\Structures\BranchIdGenerator;

class BranchIds
{
    public function ensure(array $tree)
    {
        return collect($tree)->map(function ($branch) {
            return $this->ensureIdOnBranch($branch);
        })->all();
    }

    private function ensureIdOnBranch($branch)
    {
        $id = $branch['id'] ?? BranchIdGenerator::generate();

        if ($branch['children'] ?? false) {
            $branch['children'] = $this->ensure($branch['children']);
        }

        return array_merge(['id' => $id], $branch);
    }
}
