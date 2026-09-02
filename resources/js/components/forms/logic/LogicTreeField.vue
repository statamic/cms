<script setup lang="ts">
import FieldNumber from '@/components/forms/FieldNumber.vue';
import { Icon } from '@ui';
import { categories, categoryColorClasses } from '@/components/forms/builder/categories';
import { computed, inject } from 'vue';

const props = defineProps<{
    field: any;
}>();

const fieldtypes = inject<any[]>('fieldtypes', []);

const mutedIconClass = 'text-gray-600 dark:text-gray-400';

const category = computed(() => fieldtypes.find((fieldtype) => fieldtype.handle === props.field.fieldtype)?.categories?.[0] ?? 'other');

const iconClass = computed(() => {
    if (props.field.type === 'reference') {
        return mutedIconClass;
    }

    const color = categories[category.value]?.color || 'gray';

    return categoryColorClasses[color]?.icon || mutedIconClass;
});
</script>

<template>
    <Icon
        :name="field.icon || 'generic-field'"
        :class="['size-4 shrink-0', iconClass]"
        aria-hidden="true"
    />
    <span class="linked-list__field-name min-w-0 flex-1">
        <FieldNumber :field-key="field.handle" />
        {{ __(field.config?.display) || field.handle }}
    </span>
    <span
        v-if="field.config?.hidden"
        class="inline-flex size-4 shrink-0"
        v-tooltip="__('Hidden')"
    >
        <Icon name="eye-closed" class="size-4 shrink-0 text-indigo-500 dark:text-indigo-400" aria-hidden="true" />
    </span>
    <span
        v-if="field.type === 'reference'"
        class="inline-flex size-4 shrink-0"
        v-tooltip="__('Linked Field: :reference', { reference: field.field_reference })"
    >
        <Icon name="link" class="size-4 shrink-0 text-indigo-500 dark:text-indigo-400" aria-hidden="true" />
    </span>
</template>
