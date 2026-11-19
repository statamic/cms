<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$varName = $context->emit->makeLoopVariableName($context->handle);

echo $context->emit->forEach(
    $context->handle,
    value: $varName,
    key: null,
    content: fn ($e) => $e
        ->fields($context->field->fieldtype()->fields()->all()->all())
);
