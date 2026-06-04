<fieldset class="star-rating" role="radiogroup" @if (! empty($display)) aria-label="{{ $display }}" @endif @if (! empty($allow_half_stars)) data-allow-half-stars @endif>
    @for ($star = 1; $star <= ($max_stars ?? 5); $star++)
        <span class="star-rating__star">
            @if (! empty($allow_half_stars))
                <label class="star-rating__half star-rating__half--left">
                    <input
                        id="{{ $id }}-{{ $star }}-half"
                        type="radio"
                        name="{{ $name }}"
                        value="{{ $star - 0.5 }}"
                        @if (isset($js_driver)) {!! $js_attributes !!} @endif
                        @checked((float) ($value ?? null) === (float) ($star - 0.5))
                        @required(in_array('required', $validate ?? []))
                        @if ($error)
                            aria-invalid="true" aria-describedby="{{ $id }}-error"
                        @elseif ($instructions)
                            aria-describedby="{{ $id }}-instructions"
                        @endif
                    >
                    <span aria-hidden="true">★</span>
                </label>
            @endif
            <label class="star-rating__half star-rating__half--right">
                <input
                    id="{{ $id }}-{{ $star }}"
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $star }}"
                    @if (isset($js_driver)) {!! $js_attributes !!} @endif
                    @checked((float) ($value ?? null) === (float) $star)
                    @required(in_array('required', $validate ?? []))
                    @if ($error)
                        aria-invalid="true" aria-describedby="{{ $id }}-error"
                    @elseif ($instructions)
                        aria-describedby="{{ $id }}-instructions"
                    @endif
                >
                <span aria-hidden="true">★</span>
            </label>
        </span>
    @endfor
</fieldset>
