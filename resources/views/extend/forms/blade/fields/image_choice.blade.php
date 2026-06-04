@include('statamic::forms.partials.image-choice-styles')

@php
    $imageOptions = $image_options ?? [];
    $columnCount = max(1, min(4, (int) ($columns ?? 3)));
    $isMultiple = ! empty($multiple);
    $selected = $value ?? ($isMultiple ? [] : null);
@endphp

<fieldset
    class="image-choice"
    style="--image-choice-columns: {{ $columnCount }};"
    @unless ($isMultiple) role="radiogroup" @endunless
    @if ($isMultiple) role="group" @endif
>
    @foreach ($imageOptions as $option)
        @php
            $key = $option['key'] ?? null;
            $label = $option['label'] ?? $key;
            $image = $option['image'] ?? null;
            $isSelected = $isMultiple
                ? in_array($key, is_array($selected) ? $selected : [], true)
                : $selected === $key;
        @endphp
        <label @class(['image-choice__option', 'image-choice__option--selected' => $isSelected])>
            <input
                class="image-choice__input"
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
            <span class="image-choice__card">
                <span class="image-choice__media">
                    @if ($image)
                        <img class="image-choice__image" src="{{ $image }}" alt="{{ $label }}">
                    @endif
                </span>
                @if ($label)
                    <span class="image-choice__label">{{ $label }}</span>
                @endif
            </span>
        </label>
    @endforeach
</fieldset>
