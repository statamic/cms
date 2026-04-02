<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$varName = $context->emit->makeLoopVariableName($context->handle);

echo $context->emit->forEach(
    $context->handle,
    $varName,
    content: function ($e) use ($varName) {
        return $e->isolate(fn ($e) => $e->variable($varName));
    }
);
