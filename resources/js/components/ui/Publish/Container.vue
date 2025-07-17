<script>
import createContext from '@statamic/util/createContext.js';

export const [injectContainerContext, provideContainerContext] = createContext('PublishContainer');
</script>

<script setup>
import uniqid from 'uniqid';
import { usePublishContainerStore } from '@statamic/stores/publish-container.js';
import { watch, provide, getCurrentInstance, ref, computed, onBeforeUnmount, toRef } from 'vue';
import Component from '@statamic/components/Component.js';
import { getActivePinia } from 'pinia';
import Tabs from './Tabs.vue';

const emit = defineEmits(['update:modelValue', 'update:visibleValues', 'update:modifiedFields']);

const container = getCurrentInstance();

const props = defineProps({
    name: {
        type: String,
        default: () => uniqid(),
    },
    reference: {
        type: String,
    },
    blueprint: {
        type: Object,
    },
    modelValue: {
        type: Object,
        default: () => ({}),
    },
    extraValues: {
        type: Object,
        default: () => ({}),
    },
    meta: {
        type: Object,
        default: () => ({}),
    },
    originValues: {
        type: Object,
    },
    originMeta: {
        type: Object,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
    site: {
        type: String,
    },
    modifiedFields: {
        type: Array,
    },
    trackDirtyState: {
        type: Boolean,
        default: true,
    },
    syncFieldConfirmationText: {
        type: String,
        default: () => __('Are you sure?'),
    },
    readOnly: {
        type: Boolean,
        default: false,
    },
    asConfig: {
        type: Boolean,
        default: false,
    }
});

const store = usePublishContainerStore(props.name, {
    values: clone(props.modelValue),
    extraValues: props.extraValues,
    meta: props.meta,
    originValues: props.originValues,
    originMeta: props.originMeta,
    errors: props.errors,
    localizedFields: props.modifiedFields,
    site: props.site,
    reference: props.reference,
    readOnly: props.readOnly,
});

const components = ref([]);

watch(
    () => props.modelValue,
    (values) => store.setValues(clone(values)),
    { deep: true },
);

watch(
    () => store.values,
    (values) => {
        if (JSON.stringify(values) === JSON.stringify(props.modelValue)) return;
        if (props.trackDirtyState) dirty();
        emit('update:modelValue', values);
    },
    { deep: true },
);

watch(
    () => store.visibleValues,
    (values) => emit('update:visibleValues', values),
    { deep: true },
);

watch(
    () => props.meta,
    (meta) => store.setMeta(meta),
    { deep: true },
);

watch(
    () => props.errors,
    (errors) => store.setErrors(errors),
    { deep: true },
);

watch(
    () => store.localizedFields,
    (values) => emit('update:modifiedFields', values),
    { deep: true },
);

watch(
    () => props.site,
    (site) => store.setSite(site),
);

function dirty() {
    Statamic.$dirty.add(props.name);
}

function clearDirtyState() {
    Statamic.$dirty.remove(props.name);
}

function setFieldValue(handle, value) {
    store.setDottedFieldValue({ path: handle, value });
}

function syncField(handle) {
    if (!confirm(props.syncFieldConfirmationText)) return;

    store.removeLocalizedField(handle);
    store.setDottedFieldValue({ path: handle, value: store.originValues[handle] });
    store.setDottedFieldMeta({ path: handle, value: store.originMeta[handle] });
}

function desyncField(handle) {
    store.addLocalizedField(handle);
    dirty();
}

function pushComponent(name, { props }) {
    const component = new Component(uniqid(), name, props);
    components.value.push(component);
    return component;
}

provideContainerContext({
    name: props.name,
    store,
    blueprint: props.blueprint,
    reference: props.reference,
    syncField,
    desyncField,
    container,
    components,
    asConfig: toRef(() => props.asConfig),
    isTrackingOriginValues: computed(() => !!props.originValues),
});

defineExpose({
    store,
    saving,
    saved,
    setFieldValue,
    clearDirtyState,
    pushComponent,
});

onBeforeUnmount(() => {
    store.$dispose();
    delete getActivePinia().state.value[store.$id];
});

// Backwards compatibility.
provide('store', store);
provide('storeName', props.name);
provide('publishContainer', getCurrentInstance());

// The following are shims to make things temporarily work.
function saving() {}
function saved() {
    clearDirtyState();
}
</script>

<template>
    <slot>
        <Tabs />
    </slot>
</template>
