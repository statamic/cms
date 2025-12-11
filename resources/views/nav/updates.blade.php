<li>
    <inertia-link href="{{ $item->url() }}" class="flex items-centerb gap-2 sm:gap-3 {{ $item->isActive() ? 'active' : '' }}">
        @cp_svg('icons/updates', 'size-4 shrink-0')
        <span v-pre>{{ __($item->name()) }}</span>
        <updates-badge class="-ml-1.5"></updates-badge>
    </inertia-link>
</li>
