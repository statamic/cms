<?php

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\InterfaceType;
use Statamic\Contracts\Structures\Nav;
use Statamic\Support\Str;

class NavPageInterface extends InterfaceType
{
    protected $nav;

    public function __construct(Nav $nav)
    {
        $this->nav = $nav;
        $this->attributes['name'] = static::buildName($nav);
    }

    public function fields(): array
    {
        return $this->nav->blueprint()->fields()->toGql()->all();
    }

    public static function buildName(Nav $nav): string
    {
        return 'NavPage_'.Str::studly($nav->handle());
    }
}
