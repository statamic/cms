<script>
import createContext from '@/util/createContext.js';

export const [injectFieldsContext, provideFieldsContext] = createContext('PublishFields');
</script>

<script setup>
import { toRef } from 'vue';

const props = defineProps({
    fields: {
        type: Array,
        required: true,
    },
    fieldPathPrefix: {
        type: String,
    },
    metaPathPrefix: {
        type: String,
        default: (props) => props.pathPrefix,
    },
    size: {
        type: String,
    },
});

provideFieldsContext({
    fields: toRef(() => props.fields),
    fieldPathPrefix: toRef(() => props.fieldPathPrefix),
    metaPathPrefix: toRef(() => props.metaPathPrefix),
    size: toRef(() => props.size),
});
</script>

<template>
    <slot />
</template>
