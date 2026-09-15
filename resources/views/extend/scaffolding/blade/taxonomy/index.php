<?php

/** @var \Statamic\View\Scaffolding\Emitters\BladeSourceEmitter $emit */
/** @var \Statamic\Taxonomies\Taxonomy $taxonomy */
$handle = $taxonomy->handle();

if ($taxonomy->hierarchical()) {
    $content = (string) $emit->component(
        'structure:taxonomy:'.$handle,
        fn () => '<li><a href="{{ $url }}">{{ $title }}</a></li>',
    );
} else {
    $content = (string) $emit->component(
        'taxonomy',
        fn () => '<li><a href="{{ $url }}">{{ $title }}</a></li>',
        ['from' => $handle]
    );
}

echo $emit->raw(<<<BLADE
<ul>
{$emit->indentText($content)}
</ul>
BLADE
);
