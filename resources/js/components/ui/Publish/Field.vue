<script setup>
import { computed, useTemplateRef, watch } from 'vue';
import { injectContainerContext } from './Container.vue';
import { injectFieldsContext } from './FieldsProvider.vue';
import { Field, Icon, Tooltip, Label } from '@statamic/ui';
import ShowField from '@statamic/components/field-conditions/ShowField.js';

const props = defineProps({
    config: {
        type: Object,
        required: true,
    },
});

const { store, syncField, desyncField } = injectContainerContext();
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
const isRequired = computed(() => props.config.required);
const fieldtype = useTemplateRef('fieldtype');

const fieldActions = computed(() => {
    return fieldtype.value ? fieldtype.value.fieldActions : [];
});

function valueUpdated(value) {
    store.setDottedFieldValue({ path: fullPath.value, value });
    if (isSyncable.value) desync();
}

function metaUpdated(value) {
    store.setDottedFieldMeta({ path: fullPath.value, value });
}

function replicatorPreviewUpdated(value) {
    store.setDottedFieldReplicatorPreview({ path: fullPath.value, value });
}

function focused() {
    // todo
}

function blurred() {
    // todo
}

const values = computed(() => {
    return pathPrefix ? data_get(store.values, pathPrefix) : store.values;
});

const extraValues = computed(() => {
    return pathPrefix ? data_get(store.extraValues, pathPrefix) : store.extraValues;
});

const shouldShowField = computed(() => {
    return new ShowField(store, values.value, extraValues.value).showField(props.config, fullPath.value);
});

const isLocalizable = computed(() => props.config.localizable);

const isReadOnly = computed(() => {
    if (store.isRoot === false && !isLocalizable.value) return true;

    return isLocked.value || props.config.visibility === 'read_only' || false;
});

const isLocked = computed(() => false); // todo

const isSyncable = computed(() => store.isRoot === false);
const isSynced = computed(() => isSyncable.value && !store.localizedFields.includes(fullPath.value));

function sync() {
    syncField(fullPath.value);
}

function desync() {
    desyncField(fullPath.value);
}
</script>

<template>
    <Field
        v-show="shouldShowField"
        :id="fieldId"
        :instructions="config.instructions"
        :instructions-below="config.instructions_position === 'below'"
        :required="isRequired"
        :errors="errors"
        :disabled="isReadOnly"
    >
        <template #label>
            <Label :for="fieldId">
                {{ __(config.display) }}
                <Tooltip :text="__('Localizable field')" v-if="isLocalizable">
                    <Icon name="globals" class="text-gray-400" />
                </Tooltip>
                <button v-if="!isReadOnly && isSyncable" v-show="isSynced" @click="desync">
                    <Tooltip :text="__('messages.field_synced_with_origin')">
                        <Icon name="link" class="text-gray-400" />
                    </Tooltip>
                </button>
                <button v-if="!isReadOnly && isSyncable" v-show="!isSynced" @click="sync">
                    <Tooltip :text="__('messages.field_desynced_from_origin')">
                        <Icon name="link-broken" class="text-gray-400" />
                    </Tooltip>
                </button>
            </Label>
        </template>
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
