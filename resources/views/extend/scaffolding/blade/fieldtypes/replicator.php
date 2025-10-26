<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
echo $context->emit->withCountedVariable('set', function ($setVar) use ($context) {
    $branches = collect($context->fieldtype()->flattenedSetsConfig())
        ->map(fn ($config, $set) => [
            'condition' => "\${$setVar}->type == '$set'",
            'template' => $context
                ->emit
                ->withIsolatedIteration(
                    $setVar,
                    fn ($e) => $context->generator->scaffoldFields($context->fieldtype()->fields($set)->all())
                ),
        ])
        ->values()
        ->all();

    return $context->emit->forEach(
        $context->handle,
        $setVar,
        content: fn () => $context->emit->condition($branches)
    );
});
