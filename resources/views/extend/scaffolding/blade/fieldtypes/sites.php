<?php

use Statamic\View\Scaffolding\Fieldtypes\Variables\SiteVariables;

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$maxItems = $context->field->get('max_items');

if ($maxItems === 1) {
    echo $context->emit->properties($context->handle, SiteVariables::baseVariables());

    return;
}

$entryVar = $context->emit->makeLoopVariableName($context->handle, 'site');

echo $context->emit->withCountedVariable($entryVar, fn ($varName) => $context->emit->forEach(
    $context->handle,
    $varName,
    content: fn ($emit) => $emit->variables(...SiteVariables::baseVariables())
));
