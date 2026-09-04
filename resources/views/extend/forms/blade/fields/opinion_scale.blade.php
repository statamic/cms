@php
    $middleIndex = floor((count($scale_values) - 1) / 2);
@endphp

@foreach ($scale_values as $index => $scale_value)
    <label>
        <input
            id="{{ $id }}-{{ $scale_value }}-option"
            type="radio"
            name="{{ $name }}"
            value="{{ $scale_value }}"
            @if (isset($js_driver)) {!! $js_attributes !!} @endif
            @checked($value == $scale_value)
            @required(in_array('required', $validate ?? []))
            @if ($error)
                aria-invalid="true" aria-describedby="{{ $id }}-error"
            @elseif ($instructions)
                aria-describedby="{{ $id }}-instructions"
            @endif
        >
        {{ $scale_value }}
        @if ($loop->first && ! empty($low_label))
            - {{ $low_label }}
        @elseif ($index == $middleIndex && ! empty($middle_label))
            - {{ $middle_label }}
        @elseif ($loop->last && ! empty($high_label))
            - {{ $high_label }}
        @endif
    </label>
@endforeach
