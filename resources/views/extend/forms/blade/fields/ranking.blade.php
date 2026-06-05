@php
    $options = $options ?? [];
    $ranked = is_array($value ?? null) && count($value) ? $value : array_keys($options);

    $itemClass = 'flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 dark:border-gray-700 dark:bg-gray-900';
    $rankClass = 'flex size-6 shrink-0 items-center justify-center rounded-md border border-gray-300 shadow-ui-xs bg-white text-xs font-semibold text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200';
    $labelClass = 'min-w-0 flex-1 text-sm text-gray-800 dark:text-gray-200';
@endphp

<ul class="flex flex-col gap-2" data-ranking id="{{ $id }}" role="list">
    @foreach ($ranked as $index => $key)
        @php($label = $options[$key] ?? $key)
        <li class="{{ $itemClass }}">
            <span class="{{ $rankClass }}" aria-hidden="true">{{ $index + 1 }}</span>
            <span class="{{ $labelClass }}">{{ $label }}</span>
            <input type="hidden" name="{{ $name }}[]" value="{{ $key }}">
        </li>
    @endforeach
</ul>
