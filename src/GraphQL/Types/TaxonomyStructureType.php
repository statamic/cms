<?php

namespace Statamic\GraphQL\Types;

class TaxonomyStructureType extends StructureType
{
    const NAME = 'TaxonomyStructure';

    protected $attributes = [
        'name' => self::NAME,
    ];

    protected function getTreeBranchType(): string
    {
        return TaxonomyTreeBranchType::NAME;
    }
}
