@php
    $min = isset($min) ? (int) $min : 0;
    $min = $min === 1 ? 1 : 0;
    $max = isset($max) ? (int) $max : 10;
    $max = max($min + 1, min($min + 10, $max));
    $selected = isset($value) && $value !== '' ? (int) $value : null;
@endphp

@include('statamic::forms.partials.opinion-scale-styles')

<div class="opinion-scale" data-opinion-scale id="{{ $id }}">
    <div class="opinion-scale__options" role="radiogroup" @if (! empty($display)) aria-label="{{ $display }}" @endif>
        @for ($option = $min; $option <= $max; $option++)
            <label @class([
                'opinion-scale__option',
                'opinion-scale__option--selected' => $selected === $option,
                'opinion-scale__option--first' => $option === $min,
                'opinion-scale__option--last' => $option === $max,
            ])>
                <input
                    type="radio"
                    class="opinion-scale__input"
                    name="{{ $name }}"
                    value="{{ $option }}"
                    @checked($selected === $option)
                    @required(in_array('required', $validate ?? []))
                    @if (isset($js_driver)) {!! $js_attributes !!} @endif
                    @if ($error)
                        aria-invalid="true" aria-describedby="{{ $id }}-error"
                    @elseif ($instructions)
                        aria-describedby="{{ $id }}-instructions"
                    @endif
                >
                <span class="opinion-scale__value">{{ $option }}</span>
            </label>
        @endfor
    </div>

    @if (! empty($left_label) || ! empty($center_label) || ! empty($right_label))
        <div class="opinion-scale__labels">
            @if (! empty($left_label))
                <span class="opinion-scale__label opinion-scale__label--left">{{ $left_label }}</span>
            @endif
            @if (! empty($center_label))
                <span class="opinion-scale__label opinion-scale__label--center">{{ $center_label }}</span>
            @endif
            @if (! empty($right_label))
                <span class="opinion-scale__label opinion-scale__label--right">{{ $right_label }}</span>
            @endif
        </div>
    @endif
</div>
