<script setup lang="ts">
import LogicTreeField from './LogicTreeField.vue';
import { Icon } from '@ui';

defineProps<{
    item: any;
}>();
</script>

<template>
    <div
        v-for="(field, index) in item.fields"
        :key="field._id"
        class="linked-list__fieldset-row"
    >
        <LogicTreeField :field />
        <span
            v-if="index === 0"
            v-tooltip="item.import_title ? __('Linked Fieldset: :title', { title: item.import_title }) : __('Linked Fieldset')"
            class="inline-flex size-4 shrink-0"
        >
            <Icon name="link" class="size-4 shrink-0 text-indigo-500 dark:text-indigo-400" aria-hidden="true" />
        </span>
    </div>
</template>
