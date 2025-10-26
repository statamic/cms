<?php

use Statamic\View\Scaffolding\Fieldtypes\Variables\SiteVariables;

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
$maxItems = $context->field->get('max_items');

if ($maxItems === 1) {
    echo $context->emit->pair(
        $context->variable,
        fn () => $context->emit->isolate(fn () => $context->emit->variables(...SiteVariables::baseVariables()))
    );

    return;
}

echo $context->emit->pair(
    $context->variable,
    fn () => $context->emit->isolate(fn () => $context->emit->variables(...SiteVariables::baseVariables()))
);
