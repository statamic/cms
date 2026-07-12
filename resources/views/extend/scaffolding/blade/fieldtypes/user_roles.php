<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$maxItems = $context->field->get('max_items');

if ($maxItems === 1) {
    echo $context->emit->condition([
        [
            'condition' => $context->emit->tag('user:is', ['role' => $context->variable.'->handle']),
            'template' => "User has the {{ {$context->variable}->title }} role.",
        ],
    ]);

    return;
}

echo $context->emit->withCountedVariable('role', function ($roleVar) use ($context) {
    return $context->emit->forEach(
        $context->handle,
        $roleVar,
        content: fn ($emit) => $emit->variables('handle', 'title')
    );
});
