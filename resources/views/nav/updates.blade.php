<li class="{{ $item->isActive() ? '[&_a]:text-black [&_svg]:text-black!' : '' }}">
    <a
        href="{{ $item->url() }}"
        class="flex items-center gap-3 text-gray-600 hover:text-black dark:text-gray-400 dark:hover:text-gray-200 hover:[&_svg]:text-gray-700 dark:hover:[&_svg]:text-gray-200"
    >
        @cp_svg('icons/updates', 'size-4 shrink-0')
        <span v-pre>{{ __($item->name()) }}</span>
        <updates-badge class="-ml-1.5"></updates-badge>
    </a>
</li>
