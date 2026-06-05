@php
    $min = isset($min) ? (int) $min : 0;
    $min = $min === 1 ? 1 : 0;
    $max = isset($max) ? (int) $max : 10;
    $max = max($min + 1, min($min + 10, $max));
    $selected = isset($value) && $value !== '' ? (int) $value : null;

    $optionClass = 'relative flex size-9 shrink-0 cursor-pointer items-center justify-center rounded-md border border-gray-300 shadow-ui-xs bg-white text-xs font-semibold text-gray-800 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-925';
    $selectedClass = 'border-primary bg-primary/10 text-primary dark:bg-primary/20';
@endphp

<div class="inline-flex w-fit max-w-full flex-col gap-2" data-opinion-scale id="{{ $id }}">
    <div class="flex gap-2" role="radiogroup" @if (! empty($display)) aria-label="{{ $display }}" @endif>
        @for ($option = $min; $option <= $max; $option++)
            <label @class([
                $optionClass,
                $selectedClass => $selected === $option,
            ])>
                <input
                    type="radio"
                    class="absolute pointer-events-none opacity-0"
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
                <span class="leading-none">{{ $option }}</span>
            </label>
        @endfor
    </div>

    @if (! empty($low_label) || ! empty($middle_label) || ! empty($high_label))
        <div class="grid w-full grid-cols-[1fr_auto_1fr] gap-2 text-xs text-gray-500 dark:text-gray-400">
            @if (! empty($low_label))
                <span class="col-start-1 justify-self-start text-start">{{ $low_label }}</span>
            @endif
            @if (! empty($middle_label))
                <span class="col-start-2 justify-self-center text-center">{{ $middle_label }}</span>
            @endif
            @if (! empty($high_label))
                <span class="col-start-3 justify-self-end text-end">{{ $high_label }}</span>
            @endif
        </div>
    @endif
</div>
