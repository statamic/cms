@php
    $inline = isset($inline) && $inline === true;
@endphp

<input type="hidden" name="{{ $name }}[]">
@foreach ($options as $option => $label)
    <label>
        <input
            id="{{ $id }}-{{ \Illuminate\Support\Str::slug($option) }}-option"
            type="checkbox"
            name="{{ $name }}[]"
            value="{{ $option }}"
            @if (isset($js_driver)) {!! $js_attributes !!} @endif
            @checked(in_array($option, $value ?? []))
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
