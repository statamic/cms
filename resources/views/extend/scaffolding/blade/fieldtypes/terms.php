<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */

use Statamic\Facades\Taxonomy;
use Statamic\Support\Arr;

$nestable = collect(Arr::wrap($context->field->get('taxonomies')))
    ->contains(fn ($handle) => Taxonomy::findByHandle($handle)?->nestable());

$varName = $context
    ->emit
    ->makeLoopVariableName($context->handle);

echo $context->emit->forEach(
    $context->handle,
    $varName,
    content: function ($emit) use ($nestable) {
        $content = $emit->variables('url', 'title');

        if ($nestable) {
            $content .= "\n".$emit->forEach(
                'ancestors',
                $emit->makeLoopVariableName('ancestors'),
                content: fn ($emit) => $emit->variables('url', 'title')
            );
            $content .= "\n".$emit->forEach(
                'children',
                $emit->makeLoopVariableName('children'),
                content: fn ($emit) => $emit->variables('url', 'title')
            );
        }

        return $content;
    }
);
