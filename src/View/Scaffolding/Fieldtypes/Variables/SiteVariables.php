<?php

namespace Statamic\View\Scaffolding\Fieldtypes\Variables;

class SiteVariables
{
    public static function baseVariables(): array
    {
        return [
            'handle',
            'name',
            'locale',
            'url',
        ];
    }
}
