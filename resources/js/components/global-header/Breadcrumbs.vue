<script setup>
import { Link } from '@inertiajs/vue3';
import useNavigation from '../nav/navigation.js';
import { computed, inject } from 'vue';

const { breadcrumbs: nav, setParentActive, setChildActive } = useNavigation();
const additionalBreadcrumbs = inject('layout').additionalBreadcrumbs;

const activeSection = computed(() => nav.value.find(section => section.items.some(item => item.active)));

const primaryItem = computed(() => {
    if (! activeSection.value) {
        return null;
    }

    const item = activeSection.value.items.find(item => item.active);

    if (item) item.links = activeSection.value.items.filter(i => i !== item);

    return item;
});

const secondaryItem = computed(() => {
    if (!primaryItem.value || !primaryItem.value.children) {
        return null;
    }

    const item = primaryItem.value.children.find(item => item.active);

    if (item) {
        item.links = primaryItem.value.children.filter(i => i !== item);
        item.createLabel = primaryItem.value.extra?.breadcrumbs?.create_label;
        item.createUrl = primaryItem.value.extra?.breadcrumbs?.create_url;
        item.configureUrl = primaryItem.value.extra?.breadcrumbs?.configure_url;
    }

    return item;
});

const breadcrumbs = computed(() => [
    primaryItem.value,
    secondaryItem.value,
    ...(additionalBreadcrumbs.value || []),
].filter(Boolean));

function setBreadcrumbActive(index) {
    if (index === 0) {
        setParentActive(primaryItem.value, 'breadcrumbs');
    } else if (index === 1 && secondaryItem.value) {
        setChildActive(primaryItem.value, secondaryItem.value, 'breadcrumbs');
    }
}

function setDropdownItemActive(breadcrumbIndex, linkIndex, breadcrumb) {
    if (breadcrumbIndex === 0) {
        setParentActive(breadcrumb.links[linkIndex], 'breadcrumbs');
    } else if (breadcrumbIndex === 1 && secondaryItem.value) {
        setChildActive(primaryItem.value, breadcrumb.links[linkIndex], 'breadcrumbs');
    }
}
</script>

<template>
    <div class="items-center gap-2 hidden md:flex" data-global-header-breadcrumbs>
        <div
            v-for="(breadcrumb, breadcrumbIndex) in breadcrumbs"
            :key="breadcrumbIndex"
            class="items-center gap-1 lg:gap-2 md:flex relative"
        >
            <span class="text-white/30">/</span>
            <Component
                :is="breadcrumb.url ? Link: 'span'"
                class="
                    inline-flex items-center justify-center whitespace-nowrap shrink-0
                    font-medium antialiased cursor-pointer no-underline
                    disabled:text-white/60 dark:disabled:text-white/50 disabled:cursor-not-allowed
                    bg-transparent hover:bg-gray-400/10 text-gray-900 dark:text-gray-300 dark:hover:bg-white/7 dark:hover:text-gray-200 px-3 h-8
                    text-[0.8125rem] leading-tight gap-2 rounded-lg
                    dark:text-white/85! hover:text-white! px-2! mr-1.75
                "
                :href="breadcrumb.url"
                v-text="__(breadcrumb.display)"
                @click="setBreadcrumbActive(breadcrumbIndex)"
            />

            <ui-dropdown v-if="breadcrumb.links.length" class="relative" :aria-label="`${__('More options for')} ${__(breadcrumb.display)}`">
                <template #trigger>
                    <ui-button
                        variant="ghost"
                        icon="chevron-vertical"
                        class="[&_svg]:size-3! h-8! w-4! hover:bg-gray-300/5! -ml-3 mr-1 animate-in fade-in duration-500"
                        :aria-label="`${__('Options for')} ${__(breadcrumb.display)}`"
                        aria-haspopup
                        :aria-expanded="false"
                    />
                </template>
                <ui-dropdown-header
                    class="grid grid-cols-[auto_1fr_auto] items-center"
                    :icon="breadcrumb.icon"
                    :append-icon="breadcrumb.configureUrl ? 'cog-solid' : null"
                    :append-href="breadcrumb.configureUrl"
                    role="menuitem"
                >
                    <Link
                        :href="breadcrumb.url"
                        :aria-label="`${__('Navigate to')} ${__(breadcrumb.display)}`"
                        v-text="__(breadcrumb.display)"
                        @click="setBreadcrumbActive(breadcrumbIndex)"
                    />
                </ui-dropdown-header>
                <ui-dropdown-menu role="menu" v-if="breadcrumb.links.length">
                    <ui-dropdown-item
                        v-for="(link, dropdownLinkIndex) in breadcrumb.links"
                        :key="dropdownLinkIndex"
                        :text="__(link.display)"
                        :icon="link.icon"
                        :href="link.url"
                        role="menuitem"
                        :aria-label="`${__(link.display)} - ${__('Navigate to')}`"
                        @click="setDropdownItemActive(breadcrumbIndex, dropdownLinkIndex, breadcrumb)"
                    />
                </ui-dropdown-menu>
                <ui-dropdown-footer
                    v-if="breadcrumb.createUrl"
                    icon="plus"
                    :text="__(breadcrumb.createLabel)"
                    :href="breadcrumb.createUrl"
                    role="menuitem"
                    :aria-label="`${__(breadcrumb.createLabel)} - ${__('Create new')}`"
                />
            </ui-dropdown>
        </div>
    </div>
</template>
