@php
    $imageOptions = $image_options ?? [];
    $columnCount = max(1, min(4, (int) ($columns ?? 3)));
    $gridClass = match ($columnCount) {
        1 => 'grid-cols-1',
        2 => 'grid-cols-2',
        4 => 'grid-cols-4',
        default => 'grid-cols-3',
    };
    $isMultiple = ! empty($multiple);
    $selected = $value ?? ($isMultiple ? [] : null);
    $cardClasses = 'flex flex-col gap-2 h-full p-2 rounded-lg border transition-colors border-gray-300 bg-gray-50 hover:border-gray-400 dark:border-gray-600 dark:bg-gray-900 peer-checked:border-primary peer-checked:ring-1 peer-checked:ring-primary peer-focus-visible:border-primary peer-focus-visible:ring-1 peer-focus-visible:ring-primary';
    $letterClasses = 'flex size-7 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-sm font-bold text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200';
    $allowedAspectRatios = ['1/1', '4/3', '3/2', '16/9', '3/4', '2/3'];
    $aspectRatio = in_array($aspect_ratio ?? null, $allowedAspectRatios, true) ? $aspect_ratio : '16/9';
@endphp

<fieldset
    class="grid gap-3 {{ $gridClass }}"
    @unless ($isMultiple) role="radiogroup" @endunless
    @if ($isMultiple) role="group" @endif
>
    @foreach ($imageOptions as $option)
        @php
            $key = $option['key'] ?? null;
            $label = $option['label'] ?? $key;
            $image = $option['image'] ?? null;
            $letter = $option['letter'] ?? null;
            $isSelected = $isMultiple
                ? in_array($key, is_array($selected) ? $selected : [], true)
                : $selected === $key;
        @endphp
        <label class="group cursor-pointer has-disabled:cursor-not-allowed has-disabled:opacity-60">
            <input
                class="peer sr-only"
                id="{{ $id }}-{{ \Illuminate\Support\Str::slug($key) }}-option"
                type="{{ $isMultiple ? 'checkbox' : 'radio' }}"
                name="{{ $isMultiple ? $name.'[]' : $name }}"
                value="{{ $key }}"
                @if (isset($js_driver)) {!! $js_attributes !!} @endif
                @checked($isSelected)
                @required(in_array('required', $validate ?? []))
                @if ($error)
                    aria-invalid="true" aria-describedby="{{ $id }}-error"
                @elseif ($instructions)
                    aria-describedby="{{ $id }}-instructions"
                @endif
            >
            <span class="{{ $cardClasses }}">
                <span class="flex items-center justify-center overflow-hidden rounded-md bg-gray-100 dark:bg-gray-800" style="aspect-ratio: {{ $aspectRatio }};">
                    @if ($image)
                        <img class="block size-full object-cover" src="{{ $image }}" alt="{{ $label }}">
                    @endif
                </span>
                @if ($label)
                    <span class="flex items-center justify-center gap-2">
                        @if ($letter)
                            <span class="{{ $letterClasses }}">{{ $letter }}</span>
                        @endif
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                    </span>
                @endif
            </span>
        </label>
    @endforeach
</fieldset>
