<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class CollectionTreeBranchType extends TreeBranchType
{
    const NAME = 'CollectionTreeBranch';

    public function fields(): array
    {
        return parent::fields() + [
            'page' => [
                'type' => GraphQL::type(EntryInterface::NAME),
                'resolve' => function ($branch) {
                    return $branch['page']->entry();
                },
            ],
        ];
    }
}
