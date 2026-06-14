@include('statamic::forms.partials.star-rating-styles')

@php
    $min = $min ?? 1;
    $ratingValue = $value ?? 0;
    $isUnrated = $ratingValue == 0;
@endphp

<input
    id="{{ $id }}"
    type="range"
    name="{{ $name }}"
    @class(['star-rating-input', 'star-rating-input--unrated' => $isUnrated])
    data-star-rating
    min="{{ $min }}"
    max="{{ $max_stars ?? 5 }}"
    step="{{ $step ?? 1 }}"
    value="{{ $isUnrated ? $min : $ratingValue }}"
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
