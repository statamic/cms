<script>
import createContext from '@statamic/util/createContext.js';

export const [injectContainerContext, provideContainerContext] = createContext('PublishContainer');
</script>

<script setup>
import uniqid from 'uniqid';
import { usePublishContainerStore } from '@statamic/stores/publish-container.js';
import { watch, provide, getCurrentInstance } from 'vue';

const emit = defineEmits(['updated']);

const props = defineProps({
    name: {
        type: String,
        default: () => uniqid(),
    },
    blueprint: {
        type: Object,
    },
    values: {
        type: Object,
        default: () => ({}),
    },
    meta: {
        type: Object,
        default: () => ({}),
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const store = usePublishContainerStore(props.name, {
    values: props.values,
    meta: props.meta,
    errors: props.errors,
});

watch(
    () => store.values,
    (values) => {
        // markAsDirty();
        emit('updated', values);
    },
    { deep: true },
);

watch(
    () => props.errors,
    (errors) => store.setErrors(errors),
    { deep: true },
);

provideContainerContext({
    store,
    blueprint: props.blueprint,
    someFunction: () => {
        alert('Function from container context');
    },
});

// Backwards compatibility.
provide('store', store);
provide('storeName', props.name);
provide('publishContainer', getCurrentInstance());
</script>

<template>
    <slot :values="values" />
</template>
