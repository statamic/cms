<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const form = computed(() => page.props.form);
</script>

<template>
    <Teleport to="#global-header-slot">
        <nav class="flex gap-x-2">
            <Link :href="cp_url(`forms/${form.handle}/fields`)">{{ __('Edit') }}</Link>
            <Link :href="cp_url(`forms/${form.handle}/logic`)">{{ __('Logic') }}</Link>
            <Link :href="cp_url(`forms/${form.handle}/connect`)">{{ __('Connect') }}</Link>
            <Link :href="cp_url(`forms/${form.handle}`)">{{ __('Results') }}</Link>
            <Link :href="cp_url(`forms/${form.handle}/edit`)">{{ __('Configure') }}</Link>
        </nav>
        <slot name="actions" />
    </Teleport>

    <slot />
</template>
