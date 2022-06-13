<?php

namespace Statamic\GraphQL\Types;

class NavType extends StructureType
{
    const NAME = 'Navigation';

    protected $attributes = [
        'name' => self::NAME,
    ];

    protected function getTreeBranchType(): string
    {
        return NavTreeBranchType::NAME;
    }
}
