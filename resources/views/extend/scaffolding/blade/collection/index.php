<?php

/** @var \Statamic\View\Scaffolding\Emitters\AntlersSourceEmitter $emit */
/** @var \Statamic\View\Scaffolding\TemplateGenerator $generator */
/** @var \Statamic\Entries\Collection $collection */
$content = (string) $emit->component(
    'collection',
    fn () => '<li><a href="{{ $url }}">{{ $title }}</a></li>',
    [
        'from' => $collection->handle(),
    ]
);

echo $emit->raw(<<<ANTLERS
<ul>
{$emit->indentText($content)}
</ul>
ANTLERS
);
