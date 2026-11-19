<?php

namespace Statamic\CP;

use Carbon\Carbon;
use Illuminate\Support\HtmlString;

class CarbonAsVueComponent
{
    public function __invoke(Carbon $date, ?array $options)
    {
        $attrs = collect(['of' => $date->toAtomString()])
            ->when($options, fn ($c) => $c->put('options', $options))
            ->map(fn ($value, $key) => is_array($value) ? ':'.$key.'=\''.json_encode($value).'\'' : $key.'="'.$value.'"')
            ->implode(' ');

        return new HtmlString('<date-time '.$attrs.'></date-time>');
    }
}
