<input
    id="{{ $id }}"
    type="range"
    name="{{ $name }}"
    min="0"
    max="{{ $max_stars }}"
    step="{{ $step ?? 1 }}"
    value="{{ $value ?? 0 }}"
    @if (isset($js_driver)) {!! $js_attributes !!} @endif
    @required(in_array('required', $validate ?? []))
    @if (! empty($display)) aria-label="{{ $display }}" @endif
    @if ($error)
        aria-invalid="true" aria-describedby="{{ $id }}-error"
    @elseif ($instructions)
        aria-describedby="{{ $id }}-instructions"
    @endif
>
