<script setup lang="ts">
import FieldNumber from '@/components/forms/FieldNumber.vue';
import { Icon } from '@ui';
import { categories, categoryColorClasses } from '@/components/forms/builder/categories';
import { computed } from 'vue';

const props = defineProps<{
    field: any;
}>();

const mutedIconClass = 'text-gray-600 dark:text-gray-400';

const iconClass = computed(() => {
    if (props.field.type === 'reference' || props.field.import) {
        return mutedIconClass;
    }

    const color = categories[props.field.category]?.color || 'gray';

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
        <FieldNumber :field-key="field._id" />
        {{ field.display }}
    </span>
    <span
        v-if="field.hidden"
        class="inline-flex size-4 shrink-0"
        v-tooltip="__('Hidden')"
    >
        <Icon name="eye-closed" class="size-4 shrink-0 text-indigo-500 dark:text-indigo-400" aria-hidden="true" />
    </span>
    <span
        v-if="field.type === 'reference'"
        class="inline-flex size-4 shrink-0"
        v-tooltip="__('Linked Field')"
    >
        <Icon name="link" class="size-4 shrink-0 text-indigo-500 dark:text-indigo-400" aria-hidden="true" />
    </span>
</template>
