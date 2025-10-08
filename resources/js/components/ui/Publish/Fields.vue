<script setup>
import { injectContainerContext } from './Container.vue';
import { injectFieldsContext } from './FieldsProvider.vue';
import Field from './Field.vue';

const { asConfig } = injectContainerContext();
const { fields, fieldPathPrefix } = injectFieldsContext();
</script>

<template>
    <div class="divide-y divide-gray-200 dark:divide-gray-800" :class="{ 'publish-fields': !asConfig }">
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
