<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
echo $context->emit->pair(
    $context->variable,
    fn () => $context->emit->isolate(fn () => $context->emit->variables('value'))
);
