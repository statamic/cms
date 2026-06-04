<fieldset class="star-rating" role="radiogroup" @if (! empty($display)) aria-label="{{ $display }}" @endif>
    @for ($star = 1; $star <= ($max_stars ?? 5); $star++)
        <label class="star-rating__star">
            <input
                id="{{ $id }}-{{ $star }}"
                type="radio"
                name="{{ $name }}"
                value="{{ $star }}"
                @if (isset($js_driver)) {!! $js_attributes !!} @endif
                @checked($value == $star)
                @required(in_array('required', $validate ?? []))
                @if ($error)
                    aria-invalid="true" aria-describedby="{{ $id }}-error"
                @elseif ($instructions)
                    aria-describedby="{{ $id }}-instructions"
                @endif
            >
            <span aria-hidden="true">★</span>
        </label>
    @endfor
</fieldset>
