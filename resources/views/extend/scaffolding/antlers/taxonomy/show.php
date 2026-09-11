<?php

/** @var \Statamic\View\Scaffolding\Emitters\AntlersSourceEmitter $emit */
/** @var \Statamic\View\Scaffolding\TemplateGenerator $generator */
/** @var \Statamic\Taxonomies\Taxonomy $taxonomy */
if ($taxonomy->hierarchical()) {
    echo $emit->pair('ancestors', fn ($emit) => $emit->isolate(fn () => $emit->variables('url', 'title')));
    echo "\n";
    echo $emit->pair('children', fn ($emit) => $emit->isolate(fn () => $emit->variables('url', 'title')));
    echo "\n";
}

$entryParams = $taxonomy->hierarchical() ? ['with_descendants' => 'true'] : [];

echo $emit->tag(
    'entries',
    fn () => '<li><a href="{{ url /}}">{{ title /}}</a></li>',
    $entryParams
);

if ($blueprint = $taxonomy->termBlueprint()) {
    echo "\n".$generator->scaffoldBlueprint($blueprint);
}
