<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
echo $context->emit->withContext(
    $context->handle,
    fn () => $context->emit->fields($context->field->fieldtype()->fields()->all())
);
