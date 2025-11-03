<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$fieldtype = $context->field->fieldtype();

$sets = $fieldtype->flattenedSetsConfig();

if (count($sets) === 0) {
    echo $context
        ->emit
        ->html($context->handle);

    return;
}

echo $context->emit->withCountedVariable('set', function ($setVar) use ($context, $fieldtype, $sets) {
    $branches = [];
    $hasText = false;

    foreach ($sets as $set => $config) {
        if ($set === 'text') {
            $hasText = true;
        }

        $branches[] = [
            'condition' => "\${$setVar}->type == '$set'",
            'template' => $context->emit->withIsolatedIteration($setVar, fn ($e) => $context->generator->scaffoldFields($fieldtype->fields($set)->all())),
        ];
    }

    if (! $hasText) {
        $branches[] = [
            'condition' => "\${$setVar}->type == 'text'",
            'template' => "{!! \${$setVar}->text !!}",
        ];
    }

    return $context->emit->forEach(
        $context->handle,
        $setVar,
        content: fn () => $context->emit->condition($branches)
    );
});
