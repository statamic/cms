<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
echo $context->emit->raw(<<<ANTLERS
<a href="{{ {$context->variable} }}">{{ {$context->variable}:title }}</a>
ANTLERS
);
