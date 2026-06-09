<label>
    <input type="hidden" name="{{ $name }}" value="0">
    <input
        type="checkbox"
        name="{{ $name }}"
        value="1"
        @if (isset($js_driver)) {!! $js_attributes !!} @endif
        @checked($value && $value !== '0')
        @required(in_array('required', $validate ?? []))
        @if ($error)
            aria-invalid="true" aria-describedby="{{ $id }}-error"
        @elseif ($instructions)
            aria-describedby="{{ $id }}-instructions"
        @endif
    >
    @if (isset($inline_label)) {{ $inline_label }} @endif
</label>
