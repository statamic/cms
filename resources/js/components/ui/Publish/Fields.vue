<script setup>
import { injectSectionContext } from './SectionProvider.vue';
import Field from './Field.vue';

const section = injectSectionContext();
const fields = section.fields;
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
