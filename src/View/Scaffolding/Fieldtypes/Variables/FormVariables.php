<?php

namespace Statamic\View\Scaffolding\Fieldtypes\Variables;

class FormVariables
{
    public static function baseVariables(): array
    {
        return [
            'handle',
            'title',
            'api_url',
            'honeypot',
        ];
    }
}
