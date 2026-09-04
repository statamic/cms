<script setup lang="ts">
import { Button, Dropdown, DropdownItem, DropdownMenu } from '@ui';
import type { MetaField } from './types';

defineProps<{
    fields: MetaField[];
}>();

const emit = defineEmits<{
    picked: [field: MetaField];
}>();
</script>

<template>
    <Dropdown v-if="fields.length" align="start">
        <template #trigger>
            <Button size="sm" :text="__('Add Chart')" icon="plus" />
        </template>
        <DropdownMenu>
            <DropdownItem
                v-for="field in fields"
                :key="field.handle"
                :icon="field.icon"
                :text="__(field.display)"
                @click="emit('picked', field)"
            />
        </DropdownMenu>
    </Dropdown>
</template>
