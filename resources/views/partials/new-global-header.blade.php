@php
    use function Statamic\trans as __;
@endphp

<header class="h-14 bg-gray-800 flex justify-between space-x-2 items-center text-white px-4 dark fixed top-0 inset-x-0 z-[3]">
    <div class="flex items-center gap-2 text-[0.8125rem] text-gray-300">
        <div class="flex items-center gap-2 relative">
            <button class="flex items-center group cursor-pointer" type="button" @click="toggleNav" aria-label="{{ __('Toggle Nav') }}">
                <div class="opacity-0 group-hover:opacity-100 p-1 size-7 transition-opacity duration-150 absolute inset-0 flex items-center justify-center">
                    @cp_svg('icons/light/burger', 'size-4')
                </div>
                @cp_svg('icons/statamic-mark-lime', 'size-7 group-hover:opacity-0 transition-opacity duration-150')
            </button>
            <a href="{{ route('statamic.cp.index') }}" class="text-gray-300">
                {{ config('app.name') }}
            </a>
            @if (Statamic::pro())
                <ui-badge size="sm" variant="flat" text="Pro" />
            @endif
        </div>
        <span class="text-gray-500">/</span>
        <ui-dropdown v-cloak>
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
            <ui-dropdown v-cloak>
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
        <ui-command-palette />
        <x-statamic::user-dropdown />
    </div>
</header>
