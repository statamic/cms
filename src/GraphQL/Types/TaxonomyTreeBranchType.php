<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class TaxonomyTreeBranchType extends TreeBranchType
{
    const NAME = 'TaxonomyTreeBranch';

    public function fields(): array
    {
        return array_merge(parent::fields(), [
            'term' => [
                'type' => GraphQL::type(TermInterface::NAME),
                'resolve' => function ($branch) {
                    return $branch['term'];
                },
            ],
        ]);
    }
}
