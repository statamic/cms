<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$valueVar = $context->emit->makeLoopVariableName('value');
$keyVar = $context->emit->makeLoopVariableName('key');

echo $context->emit->forEach(
    $context->handle,
    $valueVar,
    $keyVar,
    fn ($emit) => $emit->isolate(fn () => $emit->variables($keyVar, $valueVar))
);
