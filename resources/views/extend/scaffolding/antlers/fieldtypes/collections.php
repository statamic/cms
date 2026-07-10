<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
$maxItems = $context->field->get('max_items');

if ($maxItems === 1) {
    echo $context->emit->tag(
        'collection',
        fn () => $context->emit->isolate(fn () => $context->emit->variable('title')), [
            'from' => "{{$context->variable}}",
        ]
    );

    return;
}

echo $context->emit->tag(
    'collection',
    fn () => $context->emit->isolate(fn () => $context->emit->variable('title')), [
        'from' => "{{$context->variable}|piped}",
    ]
);
