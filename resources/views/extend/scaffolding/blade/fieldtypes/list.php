<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$listVar = $context->emit->makeLoopVariableName($context->handle);

echo $context->emit->withCountedVariable($listVar, fn ($varName) => $context->emit->forEach(
    $context->handle,
    $varName,
    content: fn ($emit) => $emit->isolate(fn () => $emit->variable($varName))
));
