<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
echo $context->emit->pair(
    $context->variable,
    fn ($e) => $e->isolate(
        fn ($e) => $e->fields($context->field->fieldtype()->fields()->all()->all())
    )
);
