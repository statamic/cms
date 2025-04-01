@php
    use function Statamic\trans as __;
@endphp

@if (config('statamic.cp.link_to_docs'))
    <div class="mt-12 flex justify-center text-center">
        <ui-badge
            href="{{ $url }}"
            pill
            text="{{ $text ?? __("Learn more about $topic") }}"
        />
    </div>
@endif
