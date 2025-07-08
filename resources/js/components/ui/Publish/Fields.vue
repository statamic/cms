<script setup>
import { injectContainerContext } from './Container.vue';
import { injectFieldsContext } from './FieldsProvider.vue';
import Field from './Field.vue';
import { computed } from 'vue';

const { asConfig } = injectContainerContext();
const { fields, fieldPathPrefix } = injectFieldsContext();
const isNested = computed(() => (fieldPathPrefix.value ?? '').includes('.'));
</script>

<template>
    <div :class="asConfig && !isNested ? 'publish-fields-fluid' : 'publish-fields'">
        <Field
            v-for="field in fields"
            :key="field.handle"
            :config="field"
            :class="`form-group field-w-${field.width}`"
        />
    </div>
</template>
