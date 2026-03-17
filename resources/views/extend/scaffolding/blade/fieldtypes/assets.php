<?php

use Statamic\Facades\AssetContainer;
use Statamic\View\Scaffolding\Fieldtypes\Variables\AssetVariables;

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$blueprint = AssetContainer::find($context->field->get('container'))?->blueprint();
$maxFiles = $context->field->get('max_files');

if ($maxFiles === 1) {
    echo $context->emit->withContext($context->handle,
        fn ($e) => $e
            ->variables(...AssetVariables::baseVariables())
            ->comment('Available, if the asset exists:')
            ->variables(...AssetVariables::metadataVariables())
    );

    return;
}

$varName = $context->emit->makeLoopVariableName($context->handle, 'asset');

echo $context->emit->withCountedVariable($varName, fn ($assetVar) => $context->emit->forEach($context->handle, $varName, content: function ($emit) {
    return
        $emit->variables(...AssetVariables::baseVariables())
            ->comment('Available, if the asset exists:')
            ->variables(...AssetVariables::metadataVariables());
}));
