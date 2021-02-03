<?php

namespace Statamic\GraphQL\Types;

class NavType extends StructureType
{
    const NAME = 'Navigation';

    protected $attributes = [
        'name' => self::NAME,
    ];
}
