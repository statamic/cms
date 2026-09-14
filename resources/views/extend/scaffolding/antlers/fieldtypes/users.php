<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
echo $context->emit->pair($context->variable, fn ($e) => $e->isolate(fn ($e) => $e
    ->blueprint(
        'user',
        fn ($e) => $e->comment('Recursive user fields for '.$context->variable)
    )
)
);
