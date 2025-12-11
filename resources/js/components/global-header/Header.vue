<script setup>
import Logo from './Logo.vue';
import Breadcrumbs from './Breadcrumbs.vue';
import SiteSelector from './SiteSelector.vue';
import Search from './Search.vue';
import ViewSiteButton from './ViewSiteButton.vue';
import UserDropdown from './UserDropdown.vue';
</script>

<template>
    <header class="h-14 bg-global-header-bg flex justify-between space-x-2 items-center text-white px-2 lg:px-4 fixed overflow-x-auto top-0 inset-x-0 z-(--z-index-above)">
        <a class="c-skip-link z-(--z-index-above) px-4 py-2 bg-blue-800 text-sm top-2.5 left-2.25 fixed opacity-0 -translate-y-24 focus:translate-y-0 focus:opacity-100 rounded-md" href="#main">
            {{ __('Skip to sidebar') }}
        </a>
        <a class="c-skip-link z-(--z-index-above) px-4 py-2 bg-blue-800 text-sm top-2.5 left-2.25 fixed opacity-0 -translate-y-24 focus:translate-y-0 focus:opacity-100 rounded-md" href="#main-content">
            {{ __('Skip to content') }}
        </a>
        <div class="dark flex items-center gap-2 text-[0.8125rem] text-white/85 w-full">
            <Logo />
            <Breadcrumbs />
        </div>

        <div class="dark flex-1 flex gap-1 md:gap-2 lg:gap-2 sm:gap-3 items-center justify-end shrink-0">
            <SiteSelector />
            <div class="flex items-center">
                <Search />
            </div>
            <ViewSiteButton />
            <UserDropdown />
        </div>
    </header>
</template>
