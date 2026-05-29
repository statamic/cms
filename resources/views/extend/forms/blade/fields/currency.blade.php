<input
    id="{{ $id }}"
    type="number"
    name="{{ $name }}"
    value="{{ $value }}"
    @if ($placeholder ?? false) placeholder="{{ $placeholder }}" @endif
    @if ($min ?? false) min="{{ $min }}" @endif
    @if ($max ?? false) max="{{ $max }}" @endif
    @if ($step ?? false) step="{{ $step }}" @endif
    @if ($character_limit ?? false) maxlength="{{ $character_limit }}" @endif
    @if ($autocomplete ?? false) autocomplete="{{ $autocomplete }}" @endif
    @if (isset($js_driver)) {!! $js_attributes !!} @endif
    @required(in_array('required', $validate ?? []))
    @if ($error)
        aria-invalid="true" aria-describedby="{{ $id }}-error"
    @elseif ($instructions)
        aria-describedby="{{ $id }}-instructions"
    @endif
>
