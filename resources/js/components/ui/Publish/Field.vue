<script setup>
import { computed, useTemplateRef } from 'vue';
import { injectContainerContext } from './Container.vue';
import { Field } from '@statamic/ui';

const props = defineProps({
    config: {
        type: Object,
        required: true,
    },
});

const context = injectContainerContext();
const store = context.store;
const handle = props.config.handle;

const fieldtypeComponent = computed(() => {
    return `${props.config.component || props.config.type}-fieldtype`;
});

const value = computed(() => {
    // todo: this is only getting top level.
    // need a way to get nested fields.
    return store.values[handle];
});
const meta = computed(() => {
    // todo: see value todo
    return store.meta[handle];
});
const errors = computed(() => {
    // todo: see value todo
    return store.errors[handle];
});
const fieldId = 'bob';
const namePrefix = '';
const fieldPathPrefix = '';
const isReadOnly = false;
const isRequired = computed(() => props.config.required);
const fieldtype = useTemplateRef('fieldtype');

const fieldActions = computed(() => {
    return fieldtype.value ? fieldtype.value.fieldActions : [];
});

function valueUpdated(value) {
    // todo: this is only setting the top level. see value todo.
    store.setFieldValue({ handle, value });
}

function metaUpdated(value) {
    // todo: this is only setting the top level. see value todo.
    store.setFieldMeta({ handle, value });
}

function focused() {}

function blurred() {}
</script>

<template>
    <Field
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
            :value="value"
            :meta="meta"
            :handle="handle"
            :name-prefix="namePrefix"
            :field-path-prefix="fieldPathPrefix"
            :read-only="isReadOnly"
            @update:value="valueUpdated"
            @meta-updated="metaUpdated"
            @focus="focused"
            @blur="blurred"
        />
    </Field>
</template>
