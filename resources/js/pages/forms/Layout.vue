<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const form = computed(() => page.props.form);
const basePath = computed(() => cp_url(`forms/${form.value.handle}`));

const normalizePath = (url) => {
    const withOrigin = url.startsWith('http') ? url : `${window.location.origin}${url}`;
    const pathname = new URL(withOrigin).pathname;

    return pathname.replace(/\/+$/, '') || '/';
};

const currentPath = computed(() => normalizePath(page.url));

const items = computed(() => {
    const base = normalizePath(basePath.value);
    const is = (path) => currentPath.value === path || currentPath.value.startsWith(`${path}/`);

    return [
        { label: __('Edit'), href: `${base}/fields`, active: is(`${base}/fields`) },
        { label: __('Logic'), href: `${base}/logic`, active: is(`${base}/logic`) },
        { label: __('Connect'), href: `${base}/connect`, active: is(`${base}/connect`) },
        { label: __('Results'), href: base, active: currentPath.value === base },
        { label: __('Configure'), href: `${base}/edit`, active: is(`${base}/edit`) },
    ];
});
</script>

<template>
    <Teleport to="#global-header-slot">
        <nav class="global-header-nav">
            <ul class="flex gap-x-2">
                <li v-for="item in items" :key="item.href">
                    <Link
                        :href="item.href"
                        :class="{ active: item.active }"
                        :aria-current="item.active ? 'page' : undefined"
                    >
                        {{ item.label }}
                    </Link>
                </li>
            </ul>
        </nav>
        <slot name="actions" />
    </Teleport>

    <slot />
</template>
