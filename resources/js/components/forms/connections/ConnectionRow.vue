<script setup>
import { Button, DragHandle, Dropdown, DropdownItem, DropdownMenu, Switch } from '@ui';

const emit = defineEmits(['collapsed', 'expanded', 'duplicated', 'removed', 'update:enabled']);

const props = defineProps({
    collapsed: Boolean,
    enabled: Boolean,
    hasError: Boolean,
    handleClass: String,
});

function toggleCollapsedState() {
    props.collapsed ? emit('expanded') : emit('collapsed');
}
</script>

<template>
    <div
        class="@container relative w-full rounded-lg border border-gray-300 text-base dark:border-white/10 bg-white dark:bg-gray-900 dark:inset-shadow-2xs dark:inset-shadow-black shadow-ui-sm"
        :class="{ 'border-red-500': hasError }"
    >
        <header
            class="group/header animate-border-color flex items-center show-focus-within rounded-[calc(var(--radius-lg)-1px)] px-1.5 antialiased duration-200 border-gray-300 dark:shadow-md"
            :class="{
                'bg-white dark:bg-gray-900': collapsed,
                'bg-gray-100 dark:bg-gray-925 rounded-b-none': !collapsed,
            }"
        >
            <DragHandle :class="handleClass" class="ms-1 cursor-grab [&_svg]:opacity-75 dark:[&_svg]:opacity-50" />
            <button type="button" class="show-focus-within_target flex flex-1 items-center gap-1.75 p-2 py-1.75 min-w-0 focus:outline-none cursor-pointer" @click="toggleCollapsedState">
                <slot name="header" />
            </button>
            <div class="flex items-center gap-2">
                <Switch
                    size="xs"
                    :model-value="enabled"
                    @update:model-value="emit('update:enabled', $event)"
                    v-tooltip="enabled ? __('Enabled') : __('Disabled')"
                />
                <Dropdown>
                    <template #trigger>
                        <Button icon="dots" variant="ghost" size="xs" class="me-2" :aria-label="__('Open row actions')" />
                    </template>
                    <DropdownMenu>
                        <DropdownItem
                            :text="__('Duplicate')"
                            icon="duplicate"
                            @click="emit('duplicated')"
                        />
                        <DropdownItem
                            :text="__('Delete')"
                            icon="trash"
                            variant="destructive"
                            @click="emit('removed')"
                        />
                    </DropdownMenu>
                </Dropdown>
            </div>
        </header>

        <div v-show="!collapsed" class="isolate border-t border-t-gray-300! dark:border-t-white/10!">
            <div class="p-4">
                <slot />
            </div>
        </div>
    </div>
</template>
