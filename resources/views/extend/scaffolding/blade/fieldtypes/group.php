<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
echo $context->emit->withContext(
    $context->handle,
    fn ($emit) => $emit->fields($context->fieldtype()->fields()->all())
);
