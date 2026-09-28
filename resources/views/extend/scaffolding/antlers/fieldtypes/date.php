<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
$mode = $context->field->get('mode');

if ($mode === 'range') {
    echo $context->emit
        ->variable($context->handle.':start')
        ->variable($handle.':end');

    return;
}

echo $context->emit->variable($context->handle);
