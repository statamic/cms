<script>
import createContext from '@/util/createContext.js';

export const [injectContainerContext, provideContainerContext, containerContextKey] = createContext('PublishContainer');
</script>

<script setup>
import { nanoid as uniqid } from 'nanoid';
import { watch, ref, computed, toRef, nextTick } from 'vue';
import Component from '@/components/Component.js';
import Tabs from './Tabs.vue';
import Values from '@/components/publish/Values.js';
import { data_get } from '@/bootstrap/globals.js';

const emit = defineEmits(['update:modelValue', 'update:visibleValues', 'update:modifiedFields']);

const props = defineProps({
    name: {
        type: String,
        default: () => uniqid(),
    },
	/** Reference of the item being edited. eg. entry::the-entry-id */
    reference: {
        type: String,
    },
	/** The blueprint's publish array. */
    blueprint: {
        type: Object,
		required: true,
    },
	/** The controlled publish form values. */
    modelValue: {
        type: Object,
        default: () => ({}),
    },
	/** Extra values to be made available to field conditions. */
    extraValues: {
        type: Object,
        default: () => ({}),
    },
	/** Fieldtype metadata. */
    meta: {
        type: Object,
        default: () => ({}),
    },
	/** Publish form values from the origin localization. */
    originValues: {
        type: Object,
    },
	/** Fieldtype metadata from the origin localization. */
    originMeta: {
        type: Object,
    },
	/** Validation errors. */
    errors: {
        type: Object,
        default: () => ({}),
    },
	/** The site handle of the active localization. */
    site: {
        type: String,
    },
	/** Array of field handles, indicating which fields have changed. */
    modifiedFields: {
        type: Array,
    },
	/** Determines whether dirty state tracking is enabled. */
    trackDirtyState: {
        type: Boolean,
        default: true,
    },
	/** Confirmation text when syncing a localized field with the origin value. */
	syncFieldConfirmationText: {
		type: String,
		default: () => __('Are you sure?'),
	},
	readOnly: {
		type: Boolean,
		default: false,
	},
	/** Marks it as a "config" form, which renders slightly differently. */
    asConfig: {
        type: Boolean,
        default: false,
    },
	/** Determines whether the active tab is remembered in the URL hash. */
    rememberTab: {
        type: Boolean,
        default: false,
    },
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
const direction = computed(() => Statamic.$config.get('sites').find(s => s.handle === props.site)?.direction ?? document.documentElement.dir ?? 'ltr');

const visibleValues = computed(() => {
    const omittable = Object.keys(hiddenFields.value).filter(
        (field) => hiddenFields.value[field].omitValue,
    );
    return new Values(values.value).except(omittable);
});

const revealerValues = computed(() => {
    return revealerFields.value.reduce((obj, field) => {
        obj[field] = data_get(values.value, field);
        return obj;
    }, {});
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
    () => props.modifiedFields,
    (modifiedFields) => localizedFields.value = modifiedFields || [],
);

watch(
    values,
    (values) => {
        dirty();
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

const avoidTrackingDirtyState = ref(false);
const trackingDirtyState = computed(() => props.trackDirtyState && !avoidTrackingDirtyState.value)

function dirty() {
    if (trackingDirtyState.value) Statamic.$dirty.add(props.name);
}

function clearDirtyState() {
    if (props.trackDirtyState) Statamic.$dirty.remove(props.name);
}

function withoutDirtying(callback) {
    const previous = avoidTrackingDirtyState.value;
    avoidTrackingDirtyState.value = true;
    callback();
    nextTick(() => avoidTrackingDirtyState.value = previous);
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

const provided = {
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
    revealerValues,
    localizedFields,
    meta,
    site: toRef(() => props.site),
    direction,
    errors: toRef(() => props.errors),
    readOnly: toRef(() => props.readOnly),
    previews,
    syncField,
    desyncField,
    components,
    asConfig: toRef(() => props.asConfig),
    rememberTab: toRef(() => props.rememberTab),
    isTrackingOriginValues: computed(() => !!props.originValues),
    setValues,
    setFieldValue,
    setFieldMeta,
    setFieldPreviewValue,
    setRevealerField,
    unsetRevealerField,
    setHiddenField,
    withoutDirtying,
};

provideContainerContext({ ...provided, container: provided });

defineExpose({
    name: props.name,
    values,
    saving,
    saved,
    revealerFields,
    setFieldValue,
    desyncField,
    clearDirtyState,
    pushComponent,
    visibleValues,
    setValues,
    setExtraValues,
});

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
