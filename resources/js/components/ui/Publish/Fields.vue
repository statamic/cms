<script setup>
import { injectContainerContext } from './containerContext.js';
import { injectFieldsContext } from './fieldsContext.js';
import Field from './Field.vue';
import { computed, inject } from 'vue';

const { asConfig: containerAsConfig } = injectContainerContext();
const { fields: injectedFields, asConfig: fieldsAsConfig } = injectFieldsContext();
const isFormSubmission = inject('isFormSubmission', false);

const asConfig = computed(() => fieldsAsConfig.value !== undefined ? fieldsAsConfig.value : containerAsConfig.value);

const fields = computed(() => {
    let fields = injectedFields.value;
    if (!isFormSubmission) fields = fields.filter(field => field.type !== 'hidden');
    return fields;
});
</script>

<template>
    <div :class="{
        'publish-fields': !asConfig,
        'divide-y divide-gray-200 dark:divide-gray-800': asConfig
    }">
        <Field
            v-for="field in fields"
            :key="field.handle"
            :config="field"
            :as-config="asConfig"
            :class="[
                'form-group',
                asConfig ? '' : `field-w-${field.width}`
            ]"
        />
    </div>
</template>
