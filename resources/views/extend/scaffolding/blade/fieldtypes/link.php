<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
echo $context->emit->raw(<<<BLADE
<a href="{{ {$context->variable} }}">{{ {$context->variable}['title'] }}</a>
BLADE
);
