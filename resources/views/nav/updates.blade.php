<li>
    <a href="{{ $item->url() }}" class="flex items-center gap-3 {{ $item->isActive() ? 'active' : '' }}">
        @cp_svg('icons/updates', 'size-4 shrink-0')
        <span v-pre>{{ __($item->name()) }}</span>
        <updates-badge class="-ml-1.5"></updates-badge>
    </a>
</li>
