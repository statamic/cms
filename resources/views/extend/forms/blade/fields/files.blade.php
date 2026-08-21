@php
    $isMultiple = ! isset($max_files) || $max_files !== 1;

    $fieldName = $name;

    if ($isMultiple) {
        $fieldName .= '[]';
    }
@endphp

<input
    id="{{ $id }}"
    type="file"
    name="{{ $fieldName }}"
    @if (isset($js_driver)) {!! $js_attributes !!} @endif
    @if ($isMultiple) multiple @endif
    @required(in_array('required', $validate ?? []))
    @if ($error)
        aria-invalid="true" aria-describedby="{{ $id }}-error"
    @elseif ($instructions)
        aria-describedby="{{ $id }}-instructions"
    @endif
>
