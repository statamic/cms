<script setup>
import { computed, useTemplateRef, watch, ref, inject } from 'vue';
import { injectContainerContext } from './Container.vue';
import { injectFieldsContext } from './FieldsProvider.vue';
import { useUiDirection } from '@/composables/ui-direction';
import {
    Avatar,
    Field,
    Label,
} from '@ui';
import FieldActions from '@/components/field-actions/FieldActions.vue';
import ShowField from '@/components/field-conditions/ShowField.js';
import { KEYS } from '@/components/field-conditions/Constants.js';

const props = defineProps({
    config: {
        type: Object,
        required: true,
    },
    fieldPathPrefix: {
        type: String,
    },
    metaPathPrefix: {
        type: String,
    },
});

const {
    values: containerValues,
    extraValues: containerExtraValues,
    visibleValues: containerVisibleValues,
    revealerValues,
    meta: containerMeta,
    syncField,
    desyncField,
    isTrackingOriginValues,
    originValues: containerOriginValues,
    asConfig: containerAsConfig,
    errors: containerErrors,
    readOnly: containerReadOnly,
    setFieldPreviewValue,
    localizedFields,
    setFieldValue,
    setFieldMeta,
    hiddenFields,
    setHiddenField,
    fieldLocks,
    focusField,
    blurField,
    container,
    direction: contentDirection,
} = injectContainerContext();
const {
    fieldPathPrefix: injectedFieldPathPrefix,
    metaPathPrefix: injectedMetaPathPrefix,
    readOnly: fieldsProviderReadOnly,
    asConfig: fieldsAsConfig,
} = injectFieldsContext();

const { direction } = useUiDirection();
const isFormSubmission = inject('isFormSubmission', false);

const asConfig = computed(() => fieldsAsConfig.value ?? containerAsConfig.value ?? false);
const fieldPathPrefix = computed(() => props.fieldPathPrefix || injectedFieldPathPrefix.value);
const metaPathPrefix = computed(() => props.metaPathPrefix || injectedMetaPathPrefix.value);
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

const errors = ref();
watch(
    () => containerErrors.value,
    (newErrors) => errors.value = newErrors[fullPath.value] || [],
    { immediate: true },
);

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
    desync();
}

function metaUpdated(value) {
    setFieldMeta(metaFullPath.value, value);
}

function replicatorPreviewUpdated(value) {
    setFieldPreviewValue(fullPath.value, value);
}

watch(
    () => fullPath.value,
    () => setFieldPreviewValue(fullPath.value, fieldtype.value?.replicatorPreview),
    { immediate: true }
);

function focused() {
    if (fieldPathPrefix.value) return;
    focusField(handle);
}

function blurred(event) {
    if (fieldPathPrefix.value) return;
    if (event?.currentTarget?.contains(event.relatedTarget)) return;
    blurField(handle);
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

const conditionHandles = computed(() => {
    const conditionKey = KEYS.find((k) => props.config[k]);
    if (!conditionKey) return null;
    const conditions = props.config[conditionKey];
    if (typeof conditions === 'string') return null;
    // Blueprint conditions are `{ field: 'operator value' }` objects.
    return Object.keys(conditions);
});

const hasConditions = computed(() => {
    if (props.config.visibility === 'hidden') return false;
    return KEYS.some((k) => props.config[k]);
});

const isCustomCondition = computed(() => {
    const conditionKey = KEYS.find((k) => props.config[k]);
    return conditionKey ? typeof props.config[conditionKey] === 'string' : false;
});

function evaluateShowField() {
    return new ShowField(
        values.value,
        extraValues.value,
        containerVisibleValues.value,
        revealerValues.value,
        hiddenFields.value,
        setHiddenField,
        { container },
    ).showField(props.config, fullPath.value);
}

// Targeted watching: only re-evaluate when referenced condition handles change,
// instead of depending on the entire values tree via a computed.
const shouldShowField = ref(props.config.visibility !== 'hidden');

if (hasConditions.value) {
    shouldShowField.value = evaluateShowField();

    watch(
        () => {
            if (isCustomCondition.value) return values.value;
            const handles = conditionHandles.value;
            if (!handles) return null;
            const src = values.value ?? {};
            const rootSrc = containerValues.value ?? {};
            return handles.map((handle) => {
                if (handle.startsWith('$root.') || handle.startsWith('root.')) {
                    return data_get(rootSrc, handle.replace(/^\$?root\./, ''));
                }
                return data_get(src, handle);
            });
        },
        () => {
            shouldShowField.value = evaluateShowField();
        },
        { deep: isCustomCondition.value },
    );

    watch(hiddenFields, () => {
        shouldShowField.value = evaluateShowField();
    });

    // Revealers / $parent paths / nested values may not be listed as handles —
    // also re-evaluate when revealer values change.
    watch(revealerValues, () => {
        shouldShowField.value = evaluateShowField();
    }, { deep: true });
} else if (props.config.visibility === 'hidden') {
    shouldShowField.value = evaluateShowField();
}

// Hidden fieldtypes are mounted like any other field so they take part in field
// conditions, but they only become visible on a form submission.
const isHiddenFieldtype = computed(() => props.config.type === 'hidden' && !isFormSubmission);

const shouldRenderField = computed(() => shouldShowField.value && !isHiddenFieldtype.value);

const shouldShowLabelText = computed(() => !props.config.hide_display);

// Whether the label renders anything visible. When it doesn't, we avoid rendering
// the field header entirely (so it doesn't reserve space) and instead attach a
// screen-reader-only label to the control below.
const shouldShowLabel = computed(
    () =>
        shouldShowLabelText.value || // Need to see the text
        isRequired.value || // Need to see the required asterisk
        isLocked.value || // Need to see the avatar
        isSyncable.value, // Need to see the icon
);

const shouldShowFieldPreviews = computed(() => {
    if (isHiddenFieldtype.value) return false;

    if (! props.config.replicator_preview) return false;

    return inject('showReplicatorFieldPreviews', false);
});

const isLocalizable = computed(() => props.config.localizable);

const isReadOnly = computed(() => {
    if (containerReadOnly.value) return true;
    if (fieldsProviderReadOnly.value) return true;

    if (isTrackingOriginValues.value && isSyncable.value && !isLocalizable.value) return true;

    return isLocked.value || props.config.visibility === 'read_only' || false;
});

const lockedBy = computed(() => fieldLocks.value[handle] ?? null);
const isLocked = computed(() => lockedBy.value !== null && lockedBy.value.id !== Statamic.user.id);

const isSyncable = computed(() => {
    // Only top-level fields can be synced.
    if (isNested.value) return false;

    // If origin values have been provided but the field is missing, there's nothing to sync.
    return isTrackingOriginValues.value && containerOriginValues.value.hasOwnProperty(fullPath.value)
});

const isSynced = computed(() => isSyncable.value && !localizedFields.value.includes(fullPath.value));
const isNested = computed(() => fullPath.value.includes('.'));
const rootFieldPath = computed(() => isNested.value ? fullPath.value.split('.')[0] : fullPath.value);

function sync() {
    syncField(rootFieldPath.value);
}

function desync() {
    desyncField(rootFieldPath.value);
}

const fieldtypeComponentProps = computed(() => ({
    id: fieldId.value,
    config: props.config,
    value: value.value,
    meta: meta.value,
    handle: handle,
    namePrefix: namePrefix,
    fieldPathPrefix: fieldPathPrefix.value,
    metaPathPrefix: metaPathPrefix.value,
    readOnly: isReadOnly.value,
    showFieldPreviews: shouldShowFieldPreviews.value,
}));

const fieldtypeComponentEvents = computed(() => ({
    'update:value': valueUpdated,
    'update:meta': metaUpdated,
    focus: focused,
    blur: blurred,
    replicatorPreviewUpdated: replicatorPreviewUpdated
}));
</script>

<template>
    <slot
        :fieldtypeComponent="fieldtypeComponent"
        :fieldtypeComponentProps="fieldtypeComponentProps"
        :fieldtypeComponentEvents="fieldtypeComponentEvents"
        :shouldShowField="shouldShowField"
    >
        <Field
            v-show="shouldRenderField"
            :class="`${config.type}-fieldtype`"
            :id="fieldId"
            :dir="direction"
            :instructions="config.instructions"
            :instructions-below="config.instructions_position === 'below'"
            :required="isRequired"
            :errors="errors"
            :read-only="isReadOnly"
            :inline="asConfig"
            :full-width-setting="config.full_width_setting"
            v-bind="$attrs"
        >
            <template #label v-if="shouldShowLabel">
                <Label :for="fieldId" :required="isRequired" class="relative">
                    <Transition name="lock-avatar-pop" mode="out-in">
                        <Avatar
                            v-if="isLocked"
                            :key="`lock-avatar-${handle}-${lockedBy?.id}`"
                            :user="lockedBy"
                            class="inline-flex mx-1 -start-8 -top-0.5 absolute rounded-full size-6 text-3xs"
                            v-tooltip="lockedBy.name"
                        />
                    </Transition>
                    <template v-if="shouldShowLabelText">
                        <span v-tooltip="config.handle">
                            {{ __(config.display) }}
                        </span>
                    </template>
                    <template v-else-if="config.hide_display">
                        <span class="sr-only">{{ __(config.display) }}</span>
                    </template>
                    <ui-button size="xs" inset icon="synced" variant="ghost" v-tooltip="__('messages.field_synced_with_origin')" v-if="!isReadOnly && isSyncable" v-show="isSynced" @click="desync" />
                    <ui-button size="xs" inset icon="unsynced" variant="ghost" v-tooltip="__('messages.field_desynced_from_origin')" v-if="!isReadOnly && isSyncable" v-show="!isSynced" @click="sync" />
                </Label>
            </template>
            <template #actions v-if="shouldShowFieldActions">
                <FieldActions :actions="fieldActions" />
            </template>
            <label v-if="!shouldShowLabel && config.hide_display" :for="fieldId" class="sr-only">{{ __(config.display) }}</label>
            <div class="text-xs text-red-600" v-if="!fieldtypeComponentExists && fieldtypeComponent !== 'spacer-fieldtype'">
                Component <code v-text="fieldtypeComponent"></code> does not exist.
            </div>
            <div v-if="fieldtypeComponentExists" @focusin="focused" @focusout="blurred" :class="{ 'pointer-events-none select-none': isLocked }">
                <Component
                    ref="fieldtype"
                    :is="fieldtypeComponent"
                    v-bind="fieldtypeComponentProps"
                    v-on="fieldtypeComponentEvents"
                />
            </div>
        </Field>
    </slot>
</template>

<style scoped>
.lock-avatar-pop-enter-active,
.lock-avatar-pop-leave-active {
    transition: opacity 120ms ease, transform 120ms ease;
}

.lock-avatar-pop-enter-from,
.lock-avatar-pop-leave-to {
    opacity: 0;
    transform: translateX(-5px) scale(0.85);
}
</style>
