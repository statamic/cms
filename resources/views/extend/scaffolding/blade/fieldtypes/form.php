<?php

use Statamic\View\Scaffolding\Fieldtypes\Variables\FormVariables;

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$maxItems = $context->field->get('max_items');

if ($maxItems === 1) {
    $formContent = $context->emit->raw(<<<'BLADE'
    @if (count($errors) > 0)
        <div class="bg-red-300 text-white p-2">
            @foreach ($errors as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    @if ($success)
        <div class="bg-green-300 text-white p-2">
            {{ $success }}
        </div>
    @endif

    @foreach ($fields as $field)
        <div class="p-2">
            <label>
                {{ $field['display'] }}
                @if (in_array('required', $field['validate'] ?? []))
                    <sup class="text-red">*</sup>
                @endif
            </label>
            <div class="p-1">{!! $field['field'] !!}</div>
            @if ($field['error'])
                <p class="text-gray-500">{{ $field['error'] }}</p>
            @endif
        </div>
    @endforeach
BLADE
    );
    echo $context->emit->component(
        'form:create',
        fn () => $formContent,
        [
            ':in' => "{$context->variable}->handle",
        ]
    );

    return;
}

echo $context->emit->withCountedVariable('form', fn ($varName) => $context->emit->forEach(
    $context->handle,
    $varName,
    content: fn ($emit) => $emit->variables(...FormVariables::baseVariables())
));
