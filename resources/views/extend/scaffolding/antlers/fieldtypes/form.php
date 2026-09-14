<?php

use Statamic\View\Scaffolding\Fieldtypes\Variables\FormVariables;

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
$maxItems = $context->field->get('max_items');

if ($maxItems === 1) {
    $formContent = $context->emit->raw(<<<'ANTLERS'
{{ if errors }}
    <div class="bg-red-300 text-white p-2">
        {{ errors }}
            {{ value }}<br>
        {{ /errors }}
    </div>
{{ /if }}

{{ if success }}
    <div class="bg-green-300 text-white p-2">
        {{ success }}
    </div>
{{ /if }}

{{ fields }}
    <div class="p-2">
        <label>
            {{ display }}
            {{ if validate | contains:required }}
                <sup class="text-red">*</sup>
            {{ /if }}
        </label>
        <div class="p-1">{{ field }}</div>
        {{ if error }}
            <p class="text-gray-500">{{ error }}</p>
        {{ /if }}
    </div>
{{ /fields }}
ANTLERS
    );

    echo $context->emit->tag(
        'form:create',
        fn () => $formContent,
        [
            ':in' => "$context->variable:handle",
        ]
    );

    return;
}

echo $context->emit->pair(
    $context->variable,
    fn ($e) => $e->isolate(
        fn ($e) => $e->variables(...FormVariables::baseVariables())
    )
);
