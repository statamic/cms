@php
    use function Statamic\trans as __;
@endphp

<div class="flex">
    <a
        href="{{ $url }}"
        class="-m-2 flex flex-initial items-center p-2 text-xs text-gray-700 hover:text-gray-900 dark:text-dark-175 dark:hover:text-dark-100"
    >
        <div class="svg-icon using-svg h-6 rotate-180">
            @cp_svg('icons/micro/chevron-right')
        </div>
        <span v-pre>{{ __($title) }}</span>
    </a>
</div>
