<script setup>
import { ref, computed, onUnmounted } from 'vue';
import { PublishContainer, FieldsProvider, PublishFields } from '@statamic/ui';
import { requireElevatedSessionIf } from '@statamic/components/elevated-sessions/index.js';

const props = defineProps({
    action: { type: Object, required: true },
    selections: { type: Number, required: true },
    errors: { type: Object },
    isDirty: { type: Boolean, default: false },
});

const emit = defineEmits(['confirmed']);

let confirming = ref(false);
let running = ref(false);
let fieldset = ref({ tabs: [{ fields: props.action.fields }] });
let values = ref(props.action.values);

let confirmationText = computed(() => {
    if (!props.action.confirmationText) return;
    return __n(props.action.confirmationText, props.selections);
});

let warningText = computed(() => {
    if (!props.action.warningText) return;
    return __n(props.action.warningText, props.selections);
});

let dirtyText = computed(() => {
    if (!props.isDirty) return;
    return __(props.action.dirtyWarningText);
});

let showDirtyWarning = computed(() => {
    return props.isDirty && props.action.dirtyWarningText && !props.action.bypassesDirtyWarning;
});

let runButtonText = computed(() => {
    return __n(props.action.buttonText, props.selections);
});

function onSuccess() {
    running.value = false;
    reset();
}

function onError() {
    running.value = false;
}

function confirm() {
    if (props.action.confirm) {
        confirming.value = true;
        return;
    }

    confirmed();
}

function confirmed() {
    runAction();
}

function runAction() {
    requireElevatedSessionIf(props.action.requiresElevatedSession).then(() => {
        running.value = true;
        emit('confirmed', props.action, values.value, onSuccess, onError);
    });
}

function reset() {
    confirming.value = false;
    values.value = clone(props.action.values);
}

defineExpose({
    handle: props.action.handle,
    confirm,
});
</script>

<template>
    <confirmation-modal
        v-if="confirming"
        :title="action.title"
        :danger="action.dangerous"
        :buttonText="runButtonText"
        :busy="running"
        @confirm="confirmed"
        @cancel="reset"
    >
        <div
            v-if="confirmationText"
            v-text="confirmationText"
            :class="{ 'mb-4': warningText || showDirtyWarning || action.fields.length }"
        />

        <div
            v-if="warningText"
            v-text="warningText"
            class="text-red-500"
            :class="{ 'mb-4': showDirtyWarning || action.fields.length }"
        />

        <div
            v-if="showDirtyWarning"
            v-text="dirtyText"
            class="text-red-500"
            :class="{ 'mb-4': action.fields.length }"
        />

        <PublishContainer
            v-if="action.fields.length"
            name="confirm-action"
            :blueprint="fieldset"
            v-model="values"
            :meta="action.meta"
            :errors="errors"
        >
            <FieldsProvider :fields="action.fields">
                <PublishFields />
            </FieldsProvider>
        </PublishContainer>
    </confirmation-modal>
</template>
