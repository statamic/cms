<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
echo $context->emit->comment(<<<DOCS
Refer to the YAML guide (https://statamic.dev/yaml) for help templating [{$context->handle}]
DOCS
);
