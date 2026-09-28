<?php

use Illuminate\Support\Str;

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$variables = $context->get('variables', []);
$dictionaryType = $context->get('dictionaryType', 'item');

$maxItems = $context->field->get('max_items');

if ($maxItems === 1) {
    echo $context->emit->properties(
        $context->handle,
        $variables
    );

    return;
}

$var = $context->emit->makeLoopVariableName(
    $context->handle,
    Str::singular($dictionaryType)
);

echo $context->emit->withCountedVariable(
    $var,
    fn ($varName) => $context->emit->forEach(
        $context->handle,
        $varName,
        content: fn ($emit) => $emit->variables(...$variables)
    )
);
