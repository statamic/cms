@php
    use function Statamic\trans as __;
@endphp
@foreach ($fields as $field)
    <div class="p-4">
        <label for="{{ $field['id'] }}">
            {{ $field['display'] }}

            @if (collect($field['validate'] ?? [])->contains('required'))
                <sup aria-label="{{ __('Required') }}">*</sup>
            @endif
        </label>

        <div class="p-2">{!! $field['field'] !!}</div>

        @if ($field['instructions'] ?? false)
            <p class="text-gray-500" id="{{ $field['id'] }}-instructions">
                {{ $field['instructions'] }}
            </p>
        @endif

        @if ($field['error'] ?? false)
            <p class="text-red-600" id="{{ $field['id'] }}-error">
                {{ $field['error'] }}
            </p>
        @endif
    </div>
@endforeach
