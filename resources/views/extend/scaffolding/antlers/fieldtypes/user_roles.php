<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
$maxItems = $context->field->get('max_items');

if ($maxItems === 1) {
    echo $context->emit->tag(
        'user:is',
        fn () => "User has the {{ {$context->variable}:title /}} role.",
        [
            ':role' => "{$context->variable}:handle",
        ]
    );

    return;
}

echo $context->emit->pair($context->variable,
    fn () => $context->emit->isolate(fn () => $context->emit->variables('handle', 'title'))
);
