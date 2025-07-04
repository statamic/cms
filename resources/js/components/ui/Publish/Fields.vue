<script setup>
import { injectContainerContext } from './Container.vue';
import { injectFieldsContext } from './FieldsProvider.vue';
import Field from './Field.vue';

const { asConfig } = injectContainerContext();
const { fields } = injectFieldsContext();
</script>

<template>
    <div :class="asConfig ? 'publish-fields-fluid' : 'publish-fields'">
        <Field
            v-for="field in fields"
            :key="field.handle"
            :config="field"
            :class="`form-group field-w-${field.width}`"
        />
    </div>
</template>
