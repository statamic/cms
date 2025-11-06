<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
$fieldtype = $context->fieldtype();
$sets = $fieldtype->flattenedSetsConfig();

if (count($sets) === 0) {
    echo $context
        ->emit
        ->variable($context->handle);

    return;
}

$branches = [];
$hasText = false;

foreach ($sets as $set => $config) {
    if ($set === 'text') {
        $hasText = true;
    }

    $branches[] = [
        'condition' => "type == '$set'",
        'template' => $context->emit->isolate(fn () => $context->generator->scaffoldFields($fieldtype->fields($set)->all())),
    ];
}

if (! $hasText) {
    $branches[] = [
        'condition' => "type == 'text'",
        'template' => '{{ text /}}',
    ];
}

echo $context->emit->pair(
    $context->variable,
    fn () => $context->emit->isolate(fn () => $context->emit->condition($branches))
);
