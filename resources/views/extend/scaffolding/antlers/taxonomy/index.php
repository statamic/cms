<?php

/** @var \Statamic\View\Scaffolding\Emitters\AntlersSourceEmitter $emit */
/** @var \Statamic\Taxonomies\Taxonomy $taxonomy */
$handle = $taxonomy->handle();

if ($taxonomy->hierarchical()) {
    $content = (string) $emit->tag(
        'structure:taxonomy:'.$handle,
        fn () => '<li><a href="{{ url /}}">{{ title /}}</a></li>',
    );
} else {
    $content = (string) $emit->tag(
        'taxonomy',
        fn () => '<li><a href="{{ url /}}">{{ title /}}</a></li>',
        ['from' => $handle]
    );
}

echo $emit->raw(<<<ANTLERS
<ul>
{$emit->indentText($content)}
</ul>
ANTLERS
);
