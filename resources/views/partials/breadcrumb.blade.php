@php use function Statamic\trans as __; @endphp

<div class="flex">
    <a href="{{ $url }}" class="flex-initial flex p-2 -m-2 items-center text-xs text-gray-700 hover:text-gray-900">
        <div class="h-6 rotate-180 svg-icon using-svg">
            @cp_svg('icons/micro/chevron-right')
        </div>
        <span v-pre>{{ __($title) }}</span>
    </a>
</div>
