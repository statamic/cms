<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
$variables = $context->get('variables', []);

$maxItems = $context->field->get('max_items');

if ($maxItems === 1) {
    echo $context->emit->properties(
        $context->handle,
        $variables,
    );

    return;
}

echo $context->emit->pair(
    $context->variable,
    fn () => $context->emit->isolate(fn () => $context->emit->variables(...$variables))
);
