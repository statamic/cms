<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$varName = $context
    ->emit
    ->makeLoopVariableName($context->handle);

echo $context->emit->forEach(
    $context->handle,
    $varName,
    content: function ($emit) {
        return $emit->variables('url', 'title');
    }
);
