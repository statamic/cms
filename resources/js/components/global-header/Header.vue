<script setup>
import Logo from './Logo.vue';
import Breadcrumbs from './Breadcrumbs.vue';
import SiteSelector from './SiteSelector.vue';
import Search from './Search.vue';
import SupportButton from "@/components/global-header/SupportButton.vue";
import MaxWidthButton from "@/components/global-header/MaxWidthButton.vue";
import ViewSiteButton from './ViewSiteButton.vue';
import UserDropdown from './UserDropdown.vue';
</script>

<template>
    <header data-global-header class="h-14 bg-global-header-bg flex gap-8 justify-between items-center text-white px-2 lg:px-4 fixed overflow-x-auto top-0 inset-x-0 z-(--z-index-above)">
        <a class="c-skip-link z-(--z-index-above) px-4 py-2 bg-blue-800 text-sm top-2.5 left-2.25 fixed opacity-0 -translate-y-24 focus:translate-y-0 focus:opacity-100 rounded-md" href="#main">
            {{ __('Skip to sidebar') }}
        </a>
        <a class="c-skip-link z-(--z-index-above) px-4 py-2 bg-blue-800 text-sm top-2.5 left-2.25 fixed opacity-0 -translate-y-24 focus:translate-y-0 focus:opacity-100 rounded-md" href="#main-content">
            {{ __('Skip to content') }}
        </a>
        <div
            class="dark flex-1 flex items-center gap-2 2xl:gap-3.5 [&:has(img)]:gap-3 text-[0.8125rem] text-white/85"
        >
            <Logo />
            <Breadcrumbs />
        </div>

        <div id="global-header-slot" class="global-header-slot flex items-center justify-center" />

        <div class="dark flex-1 flex gap-1 md:gap-2 lg:gap-2 sm:gap-3 items-center justify-end shrink-0">
            <div
                id="form-layout-actions"
                class="global-header-nav-actions flex items-center shrink-0 me-auto -translate-x-6 lg:-translate-x-5"
            />
            <SiteSelector />
            <div class="flex items-center">
                <Search />
            </div>
	        <SupportButton />
	        <MaxWidthButton />
            <ViewSiteButton />
            <UserDropdown />
        </div>
    </header>
</template>

<style>
    /* Hide the expand layout control when the content is equal to or smaller than the max-width wrapper, since the button would have no visible effect. */
    /* [class*="max-["] covers arbitrary max-width classes such as a blank dashboard page with a wizard. */
    body:has([data-max-width-wrapper] > :is(.max-w-page, .max-w-5xl, .max-w-4xl, .max-w-3xl, [class*="max-["])) [data-expand-layout-control] {
        display: none;
    }
</style>