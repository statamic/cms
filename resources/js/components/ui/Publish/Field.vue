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

const { store, syncField, desyncField, asConfig } = injectContainerContext();
const { fieldPathPrefix, metaPathPrefix } = injectFieldsContext();
const handle = props.config.handle;

const fieldtypeComponent = computed(() => {
    return `${props.config.component || props.config.type}-fieldtype`;
});

const fieldtypeComponentExists = computed(() => {
    return Statamic.$app.component(fieldtypeComponent.value) !== undefined;
});

const fullPath = computed(() => [fieldPathPrefix.value, handle].filter(Boolean).join('.'));
const metaFullPath = computed(() => [metaPathPrefix.value, handle].filter(Boolean).join('.'));
const value = computed(() => data_get(store.values, fullPath.value));
const meta = computed(() => {
    const key = [metaPathPrefix.value, handle].filter(Boolean).join('.');
    return data_get(store.meta, key);
});
const errors = computed(() => store.errors[fullPath.value]);
const fieldId = computed(() => `field_${fullPath.value.replaceAll('.', '_')}`);
const namePrefix = '';
const isRequired = computed(() => props.config.required);
const fieldtype = useTemplateRef('fieldtype');

const fieldActions = computed(() => {
    return fieldtype.value ? fieldtype.value.fieldActions : [];
});

function valueUpdated(value) {
    const existingValue = data_get(store.values, fullPath.value);
    if (value === existingValue) return;
    store.setDottedFieldValue({ path: fullPath.value, value });
    if (isSyncable.value) desync();
}

function metaUpdated(value) {
    store.setDottedFieldMeta({ path: metaFullPath.value, value });
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
    return fieldPathPrefix.value ? data_get(store.values, fieldPathPrefix.value) : store.values;
});

const visibleValues = computed(() => {
    return fieldPathPrefix.value ? data_get(store.visibleValues, fieldPathPrefix.value) : store.visibleValues;
});

const extraValues = computed(() => {
    return fieldPathPrefix.value ? data_get(store.extraValues, fieldPathPrefix.value) : store.extraValues;
});

const shouldShowField = computed(() => {
    return new ShowField(store, visibleValues.value, extraValues.value).showField(props.config, fullPath.value);
});

const shouldShowLabelText = computed(() => !props.config.hide_display);

const shouldShowLabel = computed(
    () =>
        shouldShowLabelText.value || // Need to see the text
        isReadOnly.value || // Need to see the "Read Only" text
        isRequired.value || // Need to see the asterisk
        isLocked.value || // Need to see the avatar
        isLocalizable.value || // Need to see the icon
        isSyncable.value, // Need to see the icon
);

const isLocalizable = computed(() => props.config.localizable);

const isReadOnly = computed(() => {
    if (store.readOnly) return true;
    if (store.isRoot === false && !isLocalizable.value) return true;

    return isLocked.value || props.config.visibility === 'read_only' || false;
});

const isLocked = computed(() => false); // todo
const isSyncable = computed(() => store.isRoot === false);
const isSynced = computed(() => isSyncable.value && !store.localizedFields.includes(fullPath.value));
const isNested = computed(() => fullPath.value.includes('.'));
const wrapperComponent = computed(() => asConfig.value && !isNested.value ? 'card' : 'div');

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
        :class="`${config.type}-fieldtype`"
        :id="fieldId"
        :instructions="config.instructions"
        :instructions-below="config.instructions_position === 'below'"
        :required="isRequired"
        :errors="errors"
        :disabled="isReadOnly"
        :as="wrapperComponent"
    >
        <template #label>
            <Label v-if="shouldShowLabel" :for="fieldId" :required="isRequired">
                <template v-if="shouldShowLabelText">
                    <Tooltip :text="config.handle" :delay="1000">
                        {{ __(config.display) }}
                    </Tooltip>
                </template>
                <ui-button size="xs" inset icon="synced" variant="ghost" v-tooltip="__('messages.field_synced_with_origin')" v-if="!isReadOnly && isSyncable" v-show="isSynced" @click="desync" />
                <ui-button size="xs" inset icon="unsynced" variant="ghost" v-tooltip="__('messages.field_desynced_from_origin')" v-if="!isReadOnly && isSyncable" v-show="!isSynced" @click="sync" />
            </Label>
        </template>
        <template #actions>
            <publish-field-actions v-if="fieldActions?.length" :actions="fieldActions" />
        </template>
        <div class="text-xs text-red-500" v-if="!fieldtypeComponentExists">
            Component <code v-text="fieldtypeComponent"></code> does not exist.
        </div>
        <Component
            v-else
            ref="fieldtype"
            :is="fieldtypeComponent"
            :id="fieldId"
            :config="config"
            :value="value"
            :meta="meta"
            :handle="handle"
            :name-prefix="namePrefix"
            :field-path-prefix="fieldPathPrefix"
            :meta-path-prefix="metaPathPrefix"
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
