<script setup>
import { inject } from 'vue';
import { Icon } from '@ui';
import Logo from './Logo.vue';
import Breadcrumbs from './Breadcrumbs.vue';
import SiteSelector from './SiteSelector.vue';
import Search from './Search.vue';
import ViewSiteButton from './ViewSiteButton.vue';
import UserDropdown from './UserDropdown.vue';

const layout = inject('layout', {});
const isMaxWidthEnabled = layout.isMaxWidthEnabled;
const toggleMaxWidth = layout.toggleMaxWidth;
</script>

<template>
    <header class="h-14 bg-global-header-bg flex justify-between space-x-2 items-center text-white px-2 lg:px-4 fixed overflow-x-auto top-0 inset-x-0 z-(--z-index-above)">
        <a class="c-skip-link z-(--z-index-above) px-4 py-2 bg-blue-800 text-sm top-2.5 left-2.25 fixed opacity-0 -translate-y-24 focus:translate-y-0 focus:opacity-100 rounded-md" href="#main">
            {{ __('Skip to sidebar') }}
        </a>
        <a class="c-skip-link z-(--z-index-above) px-4 py-2 bg-blue-800 text-sm top-2.5 left-2.25 fixed opacity-0 -translate-y-24 focus:translate-y-0 focus:opacity-100 rounded-md" href="#main-content">
            {{ __('Skip to content') }}
        </a>
        <div
            class="dark flex items-center gap-3.5 [&:has(img)]:gap-3 text-[0.8125rem] text-white/85 w-full"
        >
            <Logo />
            <Breadcrumbs />
        </div>

        <div class="dark flex-1 flex gap-1 md:gap-2 lg:gap-2 sm:gap-3 items-center justify-end shrink-0">
            <SiteSelector />
            <div class="flex items-center">
                <Search />
            </div>
            <button
                @click="toggleMaxWidth"
                :aria-label="isMaxWidthEnabled ? __('Disable max width') : __('Enable max width')"
                :title="isMaxWidthEnabled ? __('Disable max width') : __('Enable max width')"
                class="hidden [@media(min-width:1800px)]:inline-flex items-center justify-center whitespace-nowrap shrink-0 font-medium antialiased cursor-pointer no-underline disabled:text-white/60 dark:disabled:text-white/50 disabled:cursor-not-allowed [&_svg]:shrink-0 [&_svg]:text-gray-925 [&_svg]:opacity-60 dark:[&_svg]:text-white bg-transparent hover:bg-gray-400/10 text-gray-900 dark:text-gray-300 dark:hover:bg-white/15 dark:hover:text-gray-200 h-8 text-[0.8125rem] leading-tight rounded-lg px-0 gap-0 w-8 [&_svg]:size-4 -me-2 [&_svg]:text-white/85! v-popper--has-tooltip"
            >
                <Icon :name="isMaxWidthEnabled ? 'zoom-fit-screen' : 'fit-screen'" />
            </button>
            <ViewSiteButton />
            <UserDropdown />
        </div>
    </header>
</template>
