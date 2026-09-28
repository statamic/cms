<?php

use Statamic\Facades\AssetContainer;
use Statamic\View\Scaffolding\Fieldtypes\Variables\AssetVariables;

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
$blueprint = AssetContainer::find($context->field->get('container'))?->blueprint();

echo $context->emit->pair($context->variable, function ($emit) use ($blueprint) {
    return $emit->isolate(function ($emit) use ($blueprint) {
        return $emit->blueprint($blueprint)
            ->variables(...AssetVariables::baseVariables())
            ->newline()
            ->comment('Available, if the asset exists:')
            ->variables(...AssetVariables::metadataVariables());
    });
});
