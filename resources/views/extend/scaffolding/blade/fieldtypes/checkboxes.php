<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$varName = $context->emit->makeLoopVariableName($context->handle);

echo $context->emit->forEach(
    $context->handle,
    $varName,
    content: function ($emit) use ($varName) {
        return $emit->keys($varName, ['value', 'label']);
    }
);
