<?php

use Statamic\Fieldtypes\Replicator;

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */

/** @var Replicator $fieldtype */
$fieldtype = $context->fieldtype();

$branches = collect($fieldtype->flattenedSetsConfig())
    ->map(fn ($config, $set) => [
        'condition' => "type == '$set'",
        'template' => $context->emit->isolate(fn ($e) => $context->generator->scaffoldFields($fieldtype->fields($set)->all())),
    ])
    ->values()
    ->all();

echo $context->emit->pair(
    $context->variable,
    fn ($e) => $e->condition($branches)
);
