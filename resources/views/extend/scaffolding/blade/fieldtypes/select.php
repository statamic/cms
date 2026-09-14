<?php

use Statamic\View\Scaffolding\BladeScaffoldingContext;

/** @var BladeScaffoldingContext $context */
$handle = $context->handle;

echo $context->emit
    ->variable($handle)
    ->keys(
        $handle,
        ['label'],
        true
    );
