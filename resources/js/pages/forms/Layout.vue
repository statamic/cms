<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const form = page.props.form;

const navItems = [
    { label: __('Edit'), href: cp_url(`forms/${form.value.handle}/fields`) },
    { label: __('Logic'), href: cp_url(`forms/${form.value.handle}/logic`) },
    { label: __('Connect'), href: cp_url(`forms/${form.value.handle}/connect`) },
    { label: __('Results'), href: cp_url(`forms/${form.value.handle}`) },
    { label: __('Configure'), href: cp_url(`forms/${form.value.handle}/edit`) },
];

const isActive = (href) => page.url.split('?')[0] === href;
</script>

<template>
    <Teleport to="#global-header-slot">
        <nav class="global-header-nav">
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
    </Teleport>

    <slot />
</template>
