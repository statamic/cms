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

const {
    values: containerValues,
    extraValues: containerExtraValues,
    visibleValues: containerVisibleValues,
    meta: containerMeta,
    syncField,
    desyncField,
    hasOriginValues,
    asConfig,
    errors: containerErrors,
    readOnly: containerReadOnly,
    setFieldPreviewValue,
    localizedFields,
    setFieldValue,
    setFieldMeta,
    hiddenFields,
    revealerFields,
    setHiddenField,
} = injectContainerContext();
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
const value = computed(() => data_get(containerValues.value, fullPath.value));
const meta = computed(() => {
    const key = [metaPathPrefix.value, handle].filter(Boolean).join('.');
    return data_get(containerMeta.value, key);
});
const errors = computed(() => containerErrors.value[fullPath.value]);
const fieldId = computed(() => `field_${fullPath.value.replaceAll('.', '_')}`);
const namePrefix = '';
const isRequired = computed(() => props.config.required);
const fieldtype = useTemplateRef('fieldtype');

const fieldActions = computed(() => {
    return fieldtype.value ? fieldtype.value.fieldActions : [];
});

const shouldShowFieldActions = computed(() => {
    return props.config.actions && fieldActions.value?.length > 0;
});

function valueUpdated(value) {
    const existingValue = data_get(containerValues.value, fullPath.value);
    if (value === existingValue) return;
    setFieldValue(fullPath.value, value);
    if (isSyncable.value) desync();
}

function metaUpdated(value) {
    setFieldMeta(metaFullPath.value, value);
}

function replicatorPreviewUpdated(value) {
    setFieldPreviewValue(fullPath.value, value);
}

function focused() {
    // todo
}

function blurred() {
    // todo
}

const values = computed(() => {
    return fieldPathPrefix.value ? data_get(containerValues.value, fieldPathPrefix.value) : containerValues.value;
});

const visibleValues = computed(() => {
    return fieldPathPrefix.value ? data_get(containerVisibleValues.value, fieldPathPrefix.value) : containerVisibleValues.value;
});

const extraValues = computed(() => {
    return fieldPathPrefix.value ? data_get(containerExtraValues.value, fieldPathPrefix.value) : containerExtraValues.value;
});

const shouldShowField = computed(() => {
    return new ShowField(
        visibleValues.value,
        extraValues.value,
        visibleValues.value,
        hiddenFields.value,
        revealerFields.value,
        setHiddenField
    ).showField(props.config, fullPath.value);
});

const shouldShowLabelText = computed(() => !props.config.hide_display);

const shouldShowLabel = computed(
    () =>
        shouldShowLabelText.value || // Need to see the text
        isLocked.value || // Need to see the avatar
        isLocalizable.value || // Need to see the icon
        isSyncable.value, // Need to see the icon
);

const isLocalizable = computed(() => props.config.localizable);

const isReadOnly = computed(() => {
    if (containerReadOnly.value) return true;

    if (hasOriginValues.value && !isLocalizable.value) return true;

    return isLocked.value || props.config.visibility === 'read_only' || false;
});

const isLocked = computed(() => false); // todo
const isSyncable = computed(() => hasOriginValues.value);
const isSynced = computed(() => isSyncable.value && !localizedFields.value.includes(fullPath.value));
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
        :read-only="isReadOnly"
        :as="wrapperComponent"
    >
        <template #label v-if="shouldShowLabel">
            <Label :for="fieldId" :required="isRequired">
                <template v-if="shouldShowLabelText">
                    <Tooltip :text="config.handle" :delay="1000">
                        {{ __(config.display) }}
                    </Tooltip>
                </template>
                <ui-button size="xs" inset icon="synced" variant="ghost" v-tooltip="__('messages.field_synced_with_origin')" v-if="!isReadOnly && isSyncable" v-show="isSynced" @click="desync" />
                <ui-button size="xs" inset icon="unsynced" variant="ghost" v-tooltip="__('messages.field_desynced_from_origin')" v-if="!isReadOnly && isSyncable" v-show="!isSynced" @click="sync" />
            </Label>
        </template>
        <template #actions v-if="shouldShowFieldActions">
            <publish-field-actions :actions="fieldActions" />
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
