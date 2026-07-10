<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
echo $context->emit
    ->variable($context->handle)
    ->append(' ')
    ->variable($context->handle.':label');
