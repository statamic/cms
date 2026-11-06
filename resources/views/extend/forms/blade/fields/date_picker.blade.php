<input
    id="{{ $id }}"
    type="date"
    name="{{ $name }}"
    value="{{ $value ?? '' }}"
    @if (isset($placeholder)) placeholder="{{ $placeholder }}" @endif
    @if (isset($autocomplete)) autocomplete="{{ $autocomplete }}" @endif
    @if (isset($js_driver)) {!! $js_attributes !!} @endif
    @required(in_array('required', $validate ?? []))
    @if ($error)
        aria-invalid="true" aria-describedby="{{ $id }}-error"
    @elseif ($instructions)
        aria-describedby="{{ $id }}-instructions"
    @endif
>
