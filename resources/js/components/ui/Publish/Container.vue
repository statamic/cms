<script>
import createContext from '@/util/createContext.js';

export const [injectContainerContext, provideContainerContext, containerContextKey] = createContext('PublishContainer');
</script>

<script setup>
import uniqid from 'uniqid';
import { watch, provide, getCurrentInstance, ref, computed, toRef } from 'vue';
import Component from '@/components/Component.js';
import Tabs from './Tabs.vue';
import Values from '@/components/publish/Values.js';

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

const parentContainer = injectContainerContext(containerContextKey);
const values = ref(props.modelValue);
const extraValues = ref(props.extraValues);
const hiddenFields = ref({});
const revealerFields = ref([]);
const meta = ref(props.meta);
const previews = ref({});
const localizedFields = ref(props.modifiedFields || []);
const components = ref([]);

const visibleValues = computed(() => {
    const omittable = Object.keys(hiddenFields.value).filter(
        (field) => hiddenFields.value[field].omitValue,
    );
    return new Values(values.value).except(omittable);
});

const setHiddenField = (field) => {
    hiddenFields.value[field.dottedKey] = {
        hidden: field.hidden,
        omitValue: field.omitValue,
    };
};

const setRevealerField = (path) => {
    if (!revealerFields.value.includes(path)) {
        revealerFields.value.push(path);
    }
};

const unsetRevealerField = (path) => {
    const index = revealerFields.value.indexOf(path);
    if (index !== -1) {
        revealerFields.value.splice(index, 1);
    }
};
const setFieldPreviewValue = (path, value) => {
    data_set(previews.value, path + '_', value);
};

watch(
    () => props.modelValue,
    (newValues) => values.value = newValues,
);

watch(
    () => props.meta,
    (newMeta) => meta.value = newMeta,
);

watch(
    values,
    (values) => {
        if (props.trackDirtyState) dirty();
        emit('update:modelValue', values);
    },
    { deep: true },
);

watch(
    visibleValues,
    (values) => emit('update:visibleValues', values),
    { deep: true },
);

watch(
    localizedFields,
    (values) => emit('update:modifiedFields', values),
    { deep: true },
);

function dirty() {
    Statamic.$dirty.add(props.name);
}

function clearDirtyState() {
    Statamic.$dirty.remove(props.name);
}

function setValues(newValues) {
    values.value = newValues;
}

function setExtraValues(newValues) {
    extraValues.value = newValues;
}

function setFieldValue(path, value) {
    data_set(values.value, path, value);
}

function setFieldMeta(path, value) {
    data_set(meta.value, path, value);
}

function syncField(path) {
    if (!confirm(props.syncFieldConfirmationText)) return;

    removeLocalizedField(path);
    setFieldValue(path, props.originValues[path]);
    setFieldMeta(path, props.originMeta[path]);
}

function desyncField(path) {
    addLocalizedField(path);
    dirty();
}

function addLocalizedField(path) {
    if (!localizedFields.value.includes(path)) localizedFields.value.push(path);
}

function removeLocalizedField(path) {
    const index = localizedFields.value.indexOf(path);
    if (index !== -1) localizedFields.value.splice(index, 1);
}

function pushComponent(name, { props }) {
    const component = new Component(uniqid(), name, props);
    components.value.push(component);
    return component;
}

provideContainerContext({
    name: toRef(() => props.name),
    parentContainer,
    blueprint: toRef(() => props.blueprint),
    reference: toRef(() => props.reference),
    values,
    extraValues,
    visibleValues,
    originValues: toRef(() => props.originValues),
    hiddenFields,
    revealerFields,
    localizedFields,
    meta,
    site: toRef(() => props.site),
    errors: toRef(() => props.errors),
    readOnly: toRef(() => props.readOnly),
    previews,
    syncField,
    desyncField,
    container,
    components,
    asConfig: toRef(() => props.asConfig),
    isTrackingOriginValues: computed(() => !!props.originValues),
    setValues,
    setFieldValue,
    setFieldMeta,
    setFieldPreviewValue,
    setRevealerField,
    unsetRevealerField,
    setHiddenField,
});

defineExpose({
    name: props.name,
    values,
    saving,
    saved,
    revealerFields,
    setFieldValue,
    clearDirtyState,
    pushComponent,
    visibleValues,
    setValues,
    setExtraValues,
});

// Backwards compatibility.
provide('publishContainer', getCurrentInstance()); // temporarily used by ShowField.js

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
