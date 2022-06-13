<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class NavTreeBranchType extends TreeBranchType
{
    const NAME = 'NavTreeBranch';

    public function fields(): array
    {
        return parent::fields() + [
            'page' => [
                'type' => GraphQL::type(PageInterface::NAME),
            ],
        ];
    }
}
