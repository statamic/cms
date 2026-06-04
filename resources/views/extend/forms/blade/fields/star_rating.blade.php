@include('statamic::forms.partials.star-rating-styles')

<input
    id="{{ $id }}"
    type="range"
    name="{{ $name }}"
    class="star-rating-input"
    data-star-rating
    min="{{ ! empty($allow_half_stars) ? 0.5 : 1 }}"
    max="{{ $max_stars ?? 5 }}"
    step="{{ $step ?? 1 }}"
    value="{{ $value ?? (! empty($allow_half_stars) ? 0.5 : 1) }}"
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
