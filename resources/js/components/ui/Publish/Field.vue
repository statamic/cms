<script setup>
import { computed } from 'vue';
import { injectContainerContext } from './Container.vue';
import { Label, Description, Field } from '@statamic/ui';
import TheField from '../TheField.vue';

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
const namePrefix = '';
const fieldPathPrefix = '';
const isReadOnly = false;
const isRequired = computed(() => props.config.required);

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
    <TheField
        :label="config.display"
        :id="fieldName"
        :instructions="config.instructions"
        :instructions-position="config.instructions_position"
        :required="isRequired"
        :errors="errors"
    >
        <Component
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
    </TheField>

    <!--    <Field>-->
    <!--        <Label :text="config.display" :for="name" :required="isRequired" />-->
    <!--        <Description :text="config.instructions" />-->
    <!--            <Component-->
    <!--                :is="fieldtypeComponent"-->
    <!--                :value="value"-->
    <!--                :meta="meta"-->
    <!--                :handle="handle"-->
    <!--                :name-prefix="namePrefix"-->
    <!--                :field-path-prefix="fieldPathPrefix"-->
    <!--                :read-only="isReadOnly"-->
    <!--                @update:value="valueUpdated"-->
    <!--                @meta-updated="metaUpdated"-->
    <!--                @focus="focused"-->
    <!--                @blur="blurred"-->
    <!--            />-->
    <!--        <Description v-if="errors" v-for="(error, i) in errors" :key="i" :text="error" class="text-red-500" />-->
    <!--    </Field>-->
</template>
