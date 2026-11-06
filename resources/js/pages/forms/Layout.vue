<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Button } from '@ui';

const page = usePage();
const form = computed(() => page.props.form);

const can = (ability) => page.props.can?.[ability] ?? false;

const navItems = computed(() =>
    [
        can('editFields') && { label: __('Edit'), href: cp_url(`forms/${form.value.handle}/builder`) },
        can('editFields') && { label: __('Logic'), href: cp_url(`forms/${form.value.handle}/logic`) },
        can('edit') && { label: __('Connect'), href: cp_url(`forms/${form.value.handle}/connect`) },
        can('viewSubmissions') && { label: __('Results'), href: cp_url(`forms/${form.value.handle}/submissions`) },
        can('edit') && { label: __('Configure'), href: cp_url(`forms/${form.value.handle}/edit`) },
    ].filter(Boolean),
);

const currentPath = computed(() => new URL(page.url, window.location.origin).pathname);
const isActive = (href) => currentPath.value.includes(new URL(href, window.location.origin).pathname);

const activeSectionLabel = computed(() => {
    return navItems.value.find((item) => isActive(item.href))?.label ?? navItems.value[0]?.label ?? '';
});

const closeMobileNavPopover = () => {
    const popover = document.getElementById('popover-global-header-nav');
    popover?.hidePopover?.();
};
</script>

<template>
    <Teleport to="#global-header-slot">
        <div class="flex items-center justify-center">
            <div v-if="navItems.length > 1" class="global-header-nav-popover lg:hidden">
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

            <nav v-if="navItems.length > 1" class="global-header-nav hidden lg:block" :aria-label="__('form_navigation')">
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
