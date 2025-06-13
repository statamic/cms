@php
    use function Statamic\trans as __;
@endphp

@if (config('statamic.cp.link_to_docs'))
    <div class="mt-16 flex justify-center text-center">
        <div
            class="rounded-full bg-white px-6 py-2 text-sm text-gray-700 shadow-sm dark:bg-dark-900 dark:text-dark-100"
        >
            {!! $text ?? __('Learn more about :link', ['link' => '<a href="' . $url . '" target="_blank" rel="noopener noopener" class="text-blue-600 hover:text-blue-700">' . $topic . '</a>']) !!}
        </div>
    </div>
@endif
