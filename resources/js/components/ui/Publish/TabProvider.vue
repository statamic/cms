<script>
import createContext from '@/util/createContext.js';

export const [injectTabContext, provideTabContext] = createContext('PublishTab');
</script>

<script setup>
import { toRef } from 'vue';

const props = defineProps({
    tab: {
        type: Object,
        required: true,
    },
});

provideTabContext(toRef(() => props.tab));
</script>

<template>
    <slot />
</template>
