<?php

/** @var \Statamic\View\Scaffolding\Emitters\AntlersSourceEmitter $emit */
/** @var \Statamic\View\Scaffolding\TemplateGenerator $generator */
/** @var \Statamic\Entries\Collection $collection */
$collectionHandle = $collection->handle();
$blueprints = $collection->entryBlueprints();

if ($blueprints->count() == 0) {
    return;
} elseif ($blueprints->count() == 1) {
    echo $generator->scaffoldBlueprint($blueprints->first());

    return;
}

$branches = [];

foreach ($blueprints as $blueprint) {
    $content = '';

    $content = (string) $emit->blueprint($blueprint);

    $branches[] = [
        'condition' => "blueprint == '{$blueprint->handle()}'",
        'template' => $content,
    ];
}

echo $emit->condition($branches);
