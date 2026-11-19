<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$maxItems = $context->field->get('max_items');

if ($maxItems === 1) {
    echo $context->emit->component(
        'taxonomy',
        fn ($emit) => $emit->isolate(fn () => $emit->variable('title')), [
            ':from' => $context->variable,
        ]
    );

    return;
}

echo $context->emit->component(
    'taxonomy',
    fn ($emit) => $emit->isolate(fn () => $emit->variable('title')), [
        ':from' => "{$context->variable} ?? []",
    ]
);
