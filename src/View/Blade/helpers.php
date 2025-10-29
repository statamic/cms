<?php

namespace Statamic\View\Blade;

use Statamic\Fields\ArrayableString;
use Statamic\Fields\Value;
use Statamic\Fields\Values;
use Statamic\Modifiers\Modify;
use Statamic\Statamic;
use Statamic\Tags\FluentTag;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;

function value(mixed $value): mixed
{
    if ($value instanceof Value || $value instanceof ArrayableString) {
        $value = $value->value();
    } elseif ($value instanceof Values) {
        $value = $value->all();
    } elseif ($value instanceof FluentTag) {
        return value($value->fetch());
    } elseif ($value instanceof Modify) {
        return value($value->fetch());
    }

    return $value;
}

function modify(mixed $value): Modify
{
    return Statamic::modify($value);
}

function tag(string $name): FluentTag
{
    return Statamic::tag($name);
}

function void(): string
{
    return 'void::'.GlobalRuntimeState::$environmentId;
}
