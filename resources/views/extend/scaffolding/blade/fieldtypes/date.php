<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$mode = $context->field->get('mode');

if ($mode === 'range') {
    echo $context->emit
        ->keys($context->handle, ['start', 'end']);

    return;
}

echo $context
    ->emit
    ->variable($context->handle);
