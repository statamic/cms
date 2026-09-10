@php
    $isMultiple = ! empty($multiple);
    $selected = $value ?? ($isMultiple ? [] : null);
@endphp

@foreach ($options as $option)
    @php
        $key = $option['key'] ?? null;
        $label = $option['label'] ?? $key;
        $image = $option['image'] ?? null;
        $isSelected = $isMultiple
            ? in_array($key, is_array($selected) ? $selected : [], true)
            : $selected === $key;
    @endphp

    <label>
        <input
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
        <img src="{{ $image }}" alt="{{ $label }}" style="max-width: 200px; display: block;">
        {{ $label }}
    </label>
    <br>
@endforeach
