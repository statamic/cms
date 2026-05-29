@php
    use function Statamic\trans as __;

    $isMultiple = isset($multiple) && $multiple == true;
    $inline = isset($inline) && $inline === true;
    $placeholderText = $placeholder ?? __('Please select...');

    $fieldName = $name;

    if ($isMultiple) {
        $fieldName .= '[]';
    }
@endphp

<select
    id="{{ $id }}"
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
    @unless ($isMultiple)
        <option value>{{ $placeholderText }}</option>
    @endunless
    @foreach ($options as $option => $label)
        @php
            $selected = false;
            if ($isMultiple) {
                $selected = in_array($option, $value ?? []);
            } else {
                $selected = $option == $value;
            }
        @endphp
        <option
            value="{{ $option }}"
            @selected($selected)
        >{{ $label === null ? $option : $label }}</option>
    @endforeach
</select>
