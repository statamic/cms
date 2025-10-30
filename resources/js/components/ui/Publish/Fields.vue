<script setup>
import { injectContainerContext } from './Container.vue';
import { injectFieldsContext } from './FieldsProvider.vue';
import Field from './Field.vue';
import { computed } from 'vue';

const { asConfig: containerAsConfig } = injectContainerContext();
const { fields, asConfig: fieldsAsConfig } = injectFieldsContext();

const asConfig = computed(() => fieldsAsConfig.value !== undefined ? fieldsAsConfig.value : containerAsConfig.value);
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
