<script>
import createContext from '@statamic/util/createContext.js';

export const [injectFieldsContext, provideFieldsContext] = createContext('PublishFields');
</script>

<script setup>
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
});

provideFieldsContext({
    fields: props.fields,
    fieldPathPrefix: props.fieldPathPrefix,
    metaPathPrefix: props.metaPathPrefix,
});
</script>

<template>
    <slot />
</template>
