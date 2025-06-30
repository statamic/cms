@php
    use function Statamic\trans as __;
@endphp

@if (config('statamic.cp.link_to_docs'))
    <div class="mt-12 flex justify-center text-center">
        <ui-badge
            href="{{ $url }}"
            pill
            icon-append="external-link"
            text="{{ $text ?? __("Learn about $topic") }}"
        />
    </div>
@endif
