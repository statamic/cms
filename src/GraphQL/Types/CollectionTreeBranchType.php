<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class CollectionTreeBranchType extends TreeBranchType
{
    const NAME = 'CollectionTreeBranch';

    public function fields(): array
    {
        $fields = [
            'entry' => [
                'type' => GraphQL::type(EntryInterface::NAME),
                'resolve' => function ($branch) {
                    return $branch['page']->entry();
                },
            ],
        ];

        $fields['page'] = $fields['entry'];
        $fields['page']['deprecationReason'] = 'Replaced by `entry`';

        return array_merge(parent::fields(), $fields);
    }
}
