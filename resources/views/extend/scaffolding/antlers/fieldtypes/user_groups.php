<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
$maxItems = $context->field->get('max_items');

if ($maxItems === 1) {
    echo $context->emit->tag(
        'user:in',
        fn () => "User belongs to the {{ {$context->variable}:title /}} group.",
        [
            ':group' => "{$context->variable}:handle",
        ]
    );

    return;
}

echo $context->emit->pair($context->variable,
    fn () => $context->emit->isolate(fn () => $context->emit->variables('handle', 'title'))
);
