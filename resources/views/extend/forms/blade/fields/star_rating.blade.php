@include('statamic::forms.partials.star-rating-styles')

<input
    id="{{ $id }}"
    type="range"
    name="{{ $name }}"
    @class(['star-rating-input', 'star-rating-input--unrated' => ($value ?? 0) == 0])
    data-star-rating
    min="0"
    max="{{ $max_stars ?? 5 }}"
    step="{{ $step ?? 1 }}"
    value="{{ $value ?? 0 }}"
    style="--star-rating-max: {{ $max_stars ?? 5 }}; --star-rating-step: {{ $step ?? 1 }};"
    @if (isset($js_driver)) {!! $js_attributes !!} @endif
    @required(in_array('required', $validate ?? []))
    @if (! empty($display)) aria-label="{{ $display }}" @endif
    @if ($error)
        aria-invalid="true" aria-describedby="{{ $id }}-error"
    @elseif ($instructions)
        aria-describedby="{{ $id }}-instructions"
    @endif
>
