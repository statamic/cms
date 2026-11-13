<textarea
    id="{{ $id }}"
    name="{{ $name }}"
    rows="5"
    @if (isset($placeholder)) placeholder="{{ $placeholder }}" @endif
    @if (isset($character_limit)) maxlength="{{ $character_limit }}" @endif
    @if (isset($js_driver)) {!! $js_attributes !!} @endif
    @required(in_array('required', $validate ?? []))
    @if ($error)
        aria-invalid="true" aria-describedby="{{ $id }}-error"
    @elseif ($instructions)
        aria-describedby="{{ $id }}-instructions"
    @endif
>{{ $value }}</textarea>
