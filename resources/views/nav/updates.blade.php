<li class="{{ $item->isActive() ? '[&_a]:text-black [&_svg]:text-black!' : '' }}">
    <a href="{{ $item->url() }}" class="flex items-center gap-3 text-gray-600 dark:text-gray-400 hover:text-black dark:hover:text-gray-200 hover:[&_svg]:text-gray-700 dark:hover:[&_svg]:text-gray-200">
        <ui-icon name="updates" />
        <span v-pre>{{ __($item->name()) }}</span>
        <updates-badge></updates-badge>
    </a>
</li>
