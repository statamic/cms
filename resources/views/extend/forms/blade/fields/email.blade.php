<input
    id="{{ $id }}"
    type="email"
    name="{{ $name }}"
    value="{{ $value ?? '' }}"
    @if (isset($placeholder)) placeholder="{{ $placeholder }}" @endif
    @if (isset($character_limit)) maxlength="{{ $character_limit }}" @endif
    autocomplete="email"
    @if (isset($js_driver)) {!! $js_attributes !!} @endif
    @required(in_array('required', $validate ?? []))
    @if ($error)
        aria-invalid="true" aria-describedby="{{ $id }}-error"
    @elseif ($instructions)
        aria-describedby="{{ $id }}-instructions"
    @endif
>
