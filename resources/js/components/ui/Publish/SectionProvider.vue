<script>
import createContext from '@statamic/util/createContext.js';

export const [injectSectionContext, provideSectionContext] = createContext('PublishSection');
</script>

<script setup>
const props = defineProps({
    section: {
        type: Object,
        required: true,
    },
});

provideSectionContext(props.section);
</script>

<template>
    <slot />
</template>
