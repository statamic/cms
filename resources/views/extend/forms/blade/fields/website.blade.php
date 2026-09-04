<input
    id="{{ $id }}"
    type="url"
    name="{{ $name }}"
    value="{{ $value ?? '' }}"
    @if (isset($placeholder)) placeholder="{{ $placeholder }}" @endif
    @if (isset($character_limit)) maxlength="{{ $character_limit }}" @endif
    autocomplete="url"
    @if (isset($js_driver)) {!! $js_attributes !!} @endif
    @required(in_array('required', $validate ?? []))
    @if ($error)
        aria-invalid="true" aria-describedby="{{ $id }}-error"
    @elseif ($instructions)
        aria-describedby="{{ $id }}-instructions"
    @endif
>
