<script setup>
import { computed, useTemplateRef } from 'vue';
import { injectContainerContext } from './Container.vue';
import { injectFieldsContext } from './FieldsProvider.vue';
import { Field } from '@statamic/ui';
import ShowField from '@statamic/components/field-conditions/ShowField.js';

const props = defineProps({
    config: {
        type: Object,
        required: true,
    },
});

const { store } = injectContainerContext();
const { pathPrefix, metaPathPrefix } = injectFieldsContext();
const handle = props.config.handle;

const fieldtypeComponent = computed(() => {
    return `${props.config.component || props.config.type}-fieldtype`;
});

const fullPath = computed(() => [pathPrefix, handle].filter(Boolean).join('.'));
const value = computed(() => data_get(store.values, fullPath.value));
const meta = computed(() => {
    const key = [metaPathPrefix, handle].filter(Boolean).join('.');
    return data_get(store.meta, key);
});
const errors = computed(() => data_get(store.errors, fullPath.value));
const fieldId = computed(() => `field_${fullPath.value.replaceAll('.', '_')}`);
const namePrefix = '';
const isReadOnly = false;
const isRequired = computed(() => props.config.required);
const fieldtype = useTemplateRef('fieldtype');

const fieldActions = computed(() => {
    return fieldtype.value ? fieldtype.value.fieldActions : [];
});

function valueUpdated(value) {
    store.setDottedFieldValue({ path: fullPath.value, value });
}

function metaUpdated(value) {
    store.setDottedFieldMeta({ path: fullPath.value, value });
}

function replicatorPreviewUpdated(value) {
    store.setDottedFieldReplicatorPreview({ path: fullPath.value, value });
}

function focused() {}

function blurred() {}

const values = computed(() => {
    return pathPrefix ? data_get(store.values, pathPrefix) : store.values;
});

const extraValues = computed(() => {
    return pathPrefix ? data_get(store.extraValues, pathPrefix) : store.extraValues;
});

const shouldShowField = computed(() => {
    return new ShowField(store, values.value, extraValues.value).showField(props.config, fullPath.value);
});
</script>

<template>
    <Field
        v-show="shouldShowField"
        :label="config.display"
        :id="fieldId"
        :instructions="config.instructions"
        :instructions-below="config.instructions_position === 'below'"
        :required="isRequired"
        :errors="errors"
    >
        <template #actions>
            <publish-field-actions v-if="fieldActions.length" :actions="fieldActions" />
        </template>
        <Component
            ref="fieldtype"
            :is="fieldtypeComponent"
            :id="fieldId"
            :config="config"
            :value="value"
            :meta="meta"
            :handle="handle"
            :name-prefix="namePrefix"
            :field-path-prefix="pathPrefix"
            :read-only="isReadOnly"
            show-field-previews
            @update:value="valueUpdated"
            @meta-updated="metaUpdated"
            @focus="focused"
            @blur="blurred"
            @replicator-preview-updated="replicatorPreviewUpdated"
        />
    </Field>
</template>
