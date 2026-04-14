<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Button } from '@ui';

const page = usePage();
const form = computed(() => page.props.form);

const navItems = [
    { label: __('Edit'), href: cp_url(`forms/${form.value.handle}/fields`) },
    { label: __('Logic'), href: cp_url(`forms/${form.value.handle}/logic`) },
    { label: __('Connect'), href: cp_url(`forms/${form.value.handle}/connect`) },
    { label: __('Results'), href: cp_url(`forms/${form.value.handle}`) },
    { label: __('Configure'), href: cp_url(`forms/${form.value.handle}/edit`) },
];

const isActive = (href) => page.url === new URL(href, window.location.origin).pathname;

const activeSectionLabel = computed(() => {
    return navItems.find((item) => isActive(item.href))?.label ?? navItems[0]?.label ?? '';
});

const closeMobileNavPopover = () => {
    const popover = document.getElementById('popover-global-header-nav');
    popover?.hidePopover?.();
};
</script>

<template>
    <Teleport to="#global-header-slot">
        <div class="flex items-center justify-center">
            <div class="global-header-nav-popover lg:hidden">
                <Button
                    id="anchor-global-header-nav"
                    variant="ghost"
                    class="text-white! border-0! shadow-none! [&_svg]:text-white/80"
                    popovertarget="popover-global-header-nav"
                    :text="activeSectionLabel"
                    icon-append="chevron-down"
                />
                <nav id="popover-global-header-nav" popover class="global-header-nav-popover__menu">
                    <ul>
                        <li v-for="navItem in navItems" :key="`m-${navItem.href}`">
                            <Link
                                :href="navItem.href"
                                :class="{ active: isActive(navItem.href) }"
                                :aria-current="isActive(navItem.href) ? 'page' : undefined"
                                @click="closeMobileNavPopover"
                            >
                                {{ navItem.label }}
                            </Link>
                        </li>
                    </ul>
                </nav>
            </div>

            <nav class="global-header-nav hidden lg:block" :aria-label="__('form_navigation')">
                <ul>
                    <li v-for="navItem in navItems" :key="navItem.href">
                        <Link
                            :href="navItem.href"
                            :class="{ active: isActive(navItem.href) }"
                            :aria-current="isActive(navItem.href) ? 'page' : undefined"
                        >
                            {{ navItem.label }}
                        </Link>
                    </li>
                </ul>
            </nav>
        </div>
    </Teleport>

    <slot />
</template>
