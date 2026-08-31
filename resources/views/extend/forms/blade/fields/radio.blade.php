@php
    $inline = isset($inline) && $inline === true;
@endphp

@foreach ($options as $option => $label)
    <label>
        <input
            id="{{ $id }}-{{ \Illuminate\Support\Str::slug($option) }}-option"
            type="radio"
            name="{{ $name }}"
            value="{{ $option }}"
            @if (isset($js_driver)) {!! $js_attributes !!} @endif
            @checked($value == $option)
            @required(in_array('required', $validate ?? []))
            @if ($error)
                aria-invalid="true" aria-describedby="{{ $id }}-error"
            @elseif ($instructions)
                aria-describedby="{{ $id }}-instructions"
            @endif
        >
        {{ $label === null ? $option : $label }}
    </label>
    @unless ($inline) <br> @endunless
@endforeach
