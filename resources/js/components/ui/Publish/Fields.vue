<script setup>
import { injectSectionContext } from './SectionProvider.vue';
import ExampleField from './ExampleField.vue';
import Field from './Field.vue';

const section = injectSectionContext();
const fields = section.fields;
</script>

<template>
    <div class="space-y-6">
        <div v-for="field in fields">
            <Field :config="field" />
            <!--            <ExampleField :handle="field.handle" :display="field.display" :instructions="field.instructions" />-->
        </div>
    </div>
</template>
