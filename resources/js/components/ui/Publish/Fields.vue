<script setup>
import { injectFieldsContext } from './FieldsProvider.vue';
import Field from './Field.vue';

const { fields } = injectFieldsContext();
</script>

<template>
    <div class="publish-fields">
        <Field
            v-for="field in fields"
            :key="field.handle"
            :config="field"
            :class="`form-group field-w-${field.width}`"
        />
    </div>
</template>
