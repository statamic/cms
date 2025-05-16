<script>
import createContext from '@statamic/util/createContext.js';

export const [injectTabContext, provideTabContext] = createContext('PublishTab');
</script>

<script setup>
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
