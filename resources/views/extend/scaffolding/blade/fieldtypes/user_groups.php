<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$maxItems = $context->field->get('max_items');

if ($maxItems === 1) {
    echo $context->emit->condition([
        [
            'condition' => $context->emit->tag('user:in', ['group' => $context->variable.'->handle']),
            'template' => "User belongs to the {{ {$context->variable}->title }} group.",
        ],
    ]);

    return;
}

echo $context->emit->withCountedVariable('group', function ($groupVar) use ($context) {
    return $context->emit->forEach(
        $context->handle,
        $groupVar,
        content: fn ($emit) => $emit->variables('handle', 'title')
    );
});
