<script setup lang="ts">
import FieldNumber from '@/components/forms/FieldNumber.vue';
import { Icon } from '@ui';
import { computed, inject } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
    field: any;
}>();

const fieldsetTitle = computed(() => {
    const fieldsets = Object.values(usePage().props.fieldsets ?? {});

    return fieldsets.find((fieldset) => fieldset.handle === props.field.fieldset)?.title;
});

const rows = computed(() => Object.entries(props.field.previews ?? {}).map(([handle, preview]) => ({
    handle,
    display: preview.config?.display ?? handle,
    icon: preview.icon ?? 'generic-field',
})));
</script>

<template>
    <div
        v-for="row in rows"
        :key="row.handle"
        class="linked-list__fieldset-row"
    >
        <Icon :name="row.icon" class="size-4 shrink-0 text-gray-600 dark:text-gray-400" aria-hidden="true" />
        <span class="linked-list__field-name min-w-0 flex-1">
            <FieldNumber :field-key="`${field._id}:${row.handle}`" />
            {{ __(row.display) }}
        </span>
        <span
            v-tooltip="fieldsetTitle ? __('Linked Fieldset: :title', { title: __(fieldsetTitle) }) : __('Linked Fieldset')"
            class="inline-flex size-4 shrink-0"
        >
            <Icon name="link" class="size-4 shrink-0 text-indigo-500 dark:text-indigo-400" aria-hidden="true" />
        </span>
    </div>
</template>
