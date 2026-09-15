<?php

/** @var \Statamic\View\Scaffolding\Emitters\BladeSourceEmitter $emit */
/** @var \Statamic\View\Scaffolding\TemplateGenerator $generator */
/** @var \Statamic\Taxonomies\Taxonomy $taxonomy */
if ($taxonomy->hierarchical()) {
    echo $emit->forEach('ancestors', 'ancestor', content: fn ($emit) => $emit->variables('url', 'title'));
    echo "\n";
    echo $emit->forEach('children', 'child', content: fn ($emit) => $emit->variables('url', 'title'));
    echo "\n";
}

$entryParams = $taxonomy->hierarchical() ? ['with_descendants' => 'true'] : [];

echo $emit->component(
    'entries',
    fn () => '<li><a href="{{ $url }}">{{ $title }}</a></li>',
    $entryParams
);

if ($blueprint = $taxonomy->termBlueprint()) {
    echo "\n".$generator->scaffoldBlueprint($blueprint);
}
