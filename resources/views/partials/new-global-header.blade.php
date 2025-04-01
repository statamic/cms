@php
    use function Statamic\trans as __;
@endphp

<header class="h-14 bg-gray-800 flex justify-between space-x-2 items-center text-white px-4 dark fixed top-0 inset-x-0 z-[100]">
    <div class="flex items-center gap-3 text-[0.8125rem] text-gray-300">
        <a href="/" class="flex items-center gap-2">
            <ui-icon name="statamic-mark-lime" class="size-7" />
            <a href="{{ route('statamic.cp.index') }}" class="text-gray-300">
                {{ config('app.name') }}
            </a>
            @if (Statamic::pro())
                <ui-badge size="sm" variant="flat" text="Pro" />
            @endif
        </a>
        <span class="text-gray-500">/</span>
        <ui-dropdown>
            <template #trigger>
                <ui-button text="Collections" size="sm" variant="ghost" icon-append="chevron-vertical" class="[&_svg]:size-2" />
            </template>
            <ui-dropdown-header text="Collections" />
            <ui-dropdown-menu>
                <ui-dropdown-item text="Assets" icon="assets" />
                <ui-dropdown-item text="Globals" icon="globals" />
                <ui-dropdown-item text="Navigation" icon="navigation" />
                <ui-dropdown-item text="Taxonomies" icon="taxonomies" />
            </ui-dropdown-menu>
        </ui-dropdown>
        <span class="text-gray-500">/</span>
            <ui-dropdown>
            <template #trigger>
                <ui-button text="Events" size="sm" variant="ghost" icon-append="chevron-vertical" class="[&_svg]:size-2" />
            </template>
            <ui-dropdown-header text="Events" icon="collections" :link-to-config="true" />
            <ui-dropdown-menu>
                <ui-dropdown-item text="Blog" icon="collections" />
                <ui-dropdown-item text="News" icon="collections" />
                <ui-dropdown-item text="Pages" icon="collections" />
            </ui-dropdown-menu>
            <ui-dropdown-footer icon="plus" text="Create Collection" />
        </ui-dropdown>
    </div>
    <div class="flex-1 flex gap-4 items-center justify-end">
        <button type="button" aria-expanded="false" class="data-[focus-visible]:outline-focus flex items-center gap-x-2 text-xs text-gray-400 outline-none md:w-32 md:rounded-md md:py-[calc(5/16*1rem)] md:ps-2 md:pe-1.5 md:shadow-[0_1px_5px_-4px_rgba(19,19,22,0.4),0_2px_5px_rgba(32,42,54,0.06)] md:ring-1 md:ring-gray-900/10 bg-gray-900 shadow-[0_-1px_rgba(255,255,255,0.06),0_4px_8px_rgba(0,0,0,0.05),0_1px_6px_-4px_#000] ring-white/10" >
            <ui-icon name="magnifying-glass" class="size-5 flex-none text-gray-600" />
            <span class="sr-only md:not-sr-only leading-none">Search</span>
            <kbd class="ml-auto hidden self-center rounded px-[0.3125rem] py-[0.0625rem] text-[0.625rem]/4 font-medium ring-1 ring-inset bg-white/5 text-gray-400 ring-white/7.5 md:block [word-spacing:-0.15em]">
            <kbd class="font-sans">⌘ </kbd><kbd class="font-sans">K</kbd></kbd>
        </button>
        <x-statamic::user-dropdown />
    </div>
</header>
