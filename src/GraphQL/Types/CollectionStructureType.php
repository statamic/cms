<?php

namespace Statamic\GraphQL\Types;

class CollectionStructureType extends StructureType
{
    const NAME = 'CollectionStructure';

    protected $attributes = [
        'name' => self::NAME,
    ];

    protected function getTreeBranchType(): string
    {
        return CollectionTreeBranchType::NAME;
    }
}
