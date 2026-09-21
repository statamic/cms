<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */

use Statamic\Facades\Taxonomy;
use Statamic\Support\Arr;

$nestable = collect(Arr::wrap($context->field->get('taxonomies')))
    ->contains(fn ($handle) => Taxonomy::findByHandle($handle)?->nestable());

echo $context->emit->pair($context->variable, function ($emit) use ($nestable) {
    $content = $emit->isolate(fn () => $emit->variables('url', 'title'));

    if ($nestable) {
        $content .= "\n".$emit->pair('ancestors', fn ($emit) => $emit->isolate(fn () => $emit->variables('url', 'title')));
        $content .= "\n".$emit->pair('children', fn ($emit) => $emit->isolate(fn () => $emit->variables('url', 'title')));
    }

    return $content;
});
