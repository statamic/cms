<script setup>
import { Dropdown, DropdownMenu, DropdownItem, Button } from '@/components/ui';

const props = defineProps({
    widgets: { type: Array, required: true },
});

const emit = defineEmits(['picked']);
</script>

<template>
    <Dropdown align="start">
        <template #trigger>
            <Button :text="__('Add Widget')" icon="plus" />
        </template>
        <DropdownMenu>
            <DropdownItem
                v-for="widget in widgets"
                :key="widget.handle"
                :icon="widget.icon"
                :text="widget.title"
                @click="emit('picked', widget)"
            />
        </DropdownMenu>
    </Dropdown>
</template>
