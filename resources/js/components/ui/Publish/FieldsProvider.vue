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
        default: (props) => props.fieldPathPrefix,
    },
    readOnly: {
        type: Boolean,
        default: false,
    },
    asConfig: {
        type: Boolean,
        default: undefined,
    },
});

provideFieldsContext({
    fields: toRef(() => props.fields),
    fieldPathPrefix: toRef(() => props.fieldPathPrefix),
    metaPathPrefix: toRef(() => props.metaPathPrefix),
    readOnly: toRef(() => props.readOnly),
    asConfig: toRef(() => props.asConfig),
});
</script>

<template>
    <slot />
</template>
