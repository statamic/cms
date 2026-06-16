@once
    <style>
        .star-rating-input {
            --s: 1.5rem;
            --star-rating-gap: 0.4rem;
            --star-rating-max: 5;
            --star-rating-step: 1;
            --_cell: calc(var(--s) + var(--star-rating-gap));
            --_pad: calc(var(--star-rating-step) * var(--_cell) / 2);
            /* Thumb fill boundary: full steps use half a cell; half steps use half a star width. */
            --_fill-offset: calc(var(--_cell) / 2);
            --_fill: var(--color-primary);
            --_empty: var(--color-gray-600);
            /* viewBox width = 14 + 14 * (--star-rating-gap / --s) = 17.7333; path from resources/svg/icons/star.svg */
            --_star-stroke-mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2017.7333%2014%22%3E%3Cpath%20fill%3D%22none%22%20stroke%3D%22%23000%22%20stroke-width%3D%220.75%22%20stroke-linejoin%3D%22round%22%20d%3D%22M9.26344%204.34844C8.94942%203.02262%208.45287%201.8971%207.7007%200.865168%207.34583%200.378308%206.65711%200.378311%206.302%200.864976%205.54897%201.89696%205.0506%203.02254%204.73655%204.34844c-1.27708-0.0475-2.4531%200.10502-3.61903%200.49806-0.567297%200.19124-0.797993%200.88418-0.460427%201.39727C1.35657%207.30695%202.22018%208.14266%203.33681%208.8471c-0.43299%201.2339-0.63943%202.4396-0.65185%203.7051-0.00626%200.6378%200.56948%201.0938%201.15274%200.9045%201.16232-0.3774%202.18286-0.9657%203.16091-1.8293%200.97815%200.8637%202.00043%201.452%203.16369%201.8294%200.5833%200.1892%201.159-0.2668%201.1527-0.9046-0.0124-1.2655-0.2188-2.4712-0.6518-3.7051%201.1166-0.70444%201.9802-1.54015%202.6797-2.60333%200.3376-0.51309%200.1069-1.20603-0.4604-1.39727-1.1659-0.39304-2.342-0.54556-3.61906-0.49806Z%22%2F%3E%3C%2Fsvg%3E");
            --_star-fill-mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2017.7333%2014%22%3E%3Cpath%20fill%3D%22%23000%22%20d%3D%22M9.26344%204.34844C8.94942%203.02262%208.45287%201.8971%207.7007%200.865168%207.34583%200.378308%206.65711%200.378311%206.302%200.864976%205.54897%201.89696%205.0506%203.02254%204.73655%204.34844c-1.27708-0.0475-2.4531%200.10502-3.61903%200.49806-0.567297%200.19124-0.797993%200.88418-0.460427%201.39727C1.35657%207.30695%202.22018%208.14266%203.33681%208.8471c-0.43299%201.2339-0.63943%202.4396-0.65185%203.7051-0.00626%200.6378%200.56948%201.0938%201.15274%200.9045%201.16232-0.3774%202.18286-0.9657%203.16091-1.8293%200.97815%200.8637%202.00043%201.452%203.16369%201.8294%200.5833%200.1892%201.159-0.2668%201.1527-0.9046-0.0124-1.2655-0.2188-2.4712-0.6518-3.7051%201.1166-0.70444%201.9802-1.54015%202.6797-2.60333%200.3376-0.51309%200.1069-1.20603-0.4604-1.39727-1.1659-0.39304-2.342-0.54556-3.61906-0.49806Z%22%2F%3E%3C%2Fsvg%3E");

            position: relative;
            display: block;
            width: calc(var(--_cell) * var(--star-rating-max));
            max-width: 100%;
            height: var(--s);
            /* Symmetric padding aligns thumb centres with star slots (CSS-Tricks). */
            padding-inline: var(--_pad);
            box-sizing: border-box;
            appearance: none;
            cursor: pointer;
            background: transparent;
            border: none;
            mask-image: var(--_star-fill-mask);
            mask-size: var(--_cell) var(--s);
            mask-repeat: repeat;
        }

        .star-rating-input[style*='--star-rating-step: 0.5'] {
            --_fill-offset: calc((var(--s) - var(--star-rating-gap)) / 4);
        }

        .star-rating-input::before {
            content: '';
            position: absolute;
            inset: 0;
            background-color: var(--_empty);
            pointer-events: none;
            mask-image: var(--_star-stroke-mask);
            mask-size: var(--_cell) var(--s);
            mask-repeat: repeat;
        }

        :where(.dark) .star-rating-input {
            --_empty: var(--color-gray-500);
        }

        .star-rating-input:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        .star-rating-input::-webkit-slider-thumb {
            appearance: none;
            -webkit-appearance: none;
            width: 1px;
            height: 0;
            background: transparent;
            border: none;
            border-image: conic-gradient(at calc(50% + var(--_fill-offset)), transparent 50%, var(--_fill) 0) fill 0 // var(--s) 500px;
        }

        .star-rating-input::-moz-range-thumb {
            appearance: none;
            width: 1px;
            height: 0;
            background: transparent;
            border: none;
            border-image: conic-gradient(at calc(50% + var(--_fill-offset)), transparent 50%, var(--_fill) 0) fill 0 // var(--s) 500px;
        }

        .star-rating-input.star-rating-input--unrated::-webkit-slider-thumb {
            border-image: conic-gradient(transparent 0) fill 0 // var(--s) 500px;
        }

        .star-rating-input.star-rating-input--unrated::-moz-range-thumb {
            border-image: conic-gradient(transparent 0) fill 0 // var(--s) 500px;
        }

        .star-rating-input::-webkit-slider-runnable-track {
            appearance: none;
            -webkit-appearance: none;
            height: var(--s);
            background: transparent;
        }

        .star-rating-input::-moz-range-track {
            appearance: none;
            height: var(--s);
            background: transparent;
            border: none;
        }
    </style>
@endonce

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
