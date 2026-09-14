<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$maxItems = $context->field->get('max_items');

if ($maxItems === 1) {
    $content = $context->emit->component(
        'nav',
        fn ($e) => $e->indentText('<li><a href="{{ $url }}">{{ $title }}</a></li>'),
        [
            ':handle' => $context->variable,
        ]
    );

    echo $context->emit->append('<ul>')
        ->append($context->emit->indentText($content))
        ->append('</ul>');

    return;
}

echo $context->emit->withCountedVariable('nav', function ($navVar) use ($context) {

    $content = $context->emit->component(
        'nav',
        fn ($e) => $e->indentText('<li><a href="{{ $url }}">{{ $title }}</a></li>'),
        [
            ':handle' => $navVar,
        ]
    );

    return $context->emit->forEach(
        $context->handle,
        $navVar,
        content: fn ($e) => $e->append('<ul>')
            ->append($e->indentText($content))
            ->append('</ul>')
    );
});
