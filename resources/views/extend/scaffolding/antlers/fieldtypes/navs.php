<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
$maxItems = $context->field->get('max_items');

if ($maxItems === 1) {
    $content = $context->emit->tag(
        'nav',
        fn () => '<li><a href="{{ url }}">{{ title }}</a></li>',
        [
            ':handle' => "{$context->variable}:handle",
        ]
    );

    echo $context->emit->raw(<<<HTML
<ul>
{$context->emit->indentText($content)}
</ul>
HTML
    );

    return;
}

echo $context->emit->pair(
    $context->variable,
    function ($e) {
        $content = $e->tag(
            'nav',
            fn () => '<li><a href="{{ url }}">{{ title }}</a></li>',
            [
                ':handle' => 'handle',
            ]
        );

        return $e->raw(<<<HTML
<ul>
{$e->indentText($content)}
</ul>
HTML
        );
    }
);
