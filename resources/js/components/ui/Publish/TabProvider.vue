<script setup>
import { provideTabContext } from './tab-context.js';

const props = defineProps({
    tab: {
        type: Object,
        required: true,
    },
});

provideTabContext(props.tab);
</script>

<template>
    <slot />
</template>
