<template>
    <header class="fixed inset-x-0 top-0 z-max flex items-center justify-between bg-gray-50 dark:bg-gray-900 px-4 shadow-ui-lg">
        <ui-heading class="shrink-0" :text="__(title)" />
        <div class="flex min-w-max items-center gap-4">
            <slot />
        </div>
        <div class="flex items-center justify-end py-2.5">
            <Dropdown class="mr-2">
                <template #trigger>
                    <Button icon="ui/dots" variant="ghost" size="xs" :aria-label="__('Open dropdown menu')" />
                </template>
                <DropdownMenu>
                    <DropdownItem
                        v-if="fieldActions.length"
                        v-for="action in fieldActions"
                        :text="action.title"
                        :variant="action.dangerous ? 'destructive' : 'default'"
                        @click="action.run(action)"
                    />
                </DropdownMenu>
            </Dropdown>
            <button
                class="btn-quick-action"
                v-for="(action, index) in fieldActions.filter((a) => a.quick)"
                :key="index"
                v-tooltip="action.title"
                @click="action.run()"
            >
                <ui-icon :name="action.icon" class="size-3.5 text-gray-400 dark:text-gray-600" />
            </button>
        </div>
    </header>
</template>

<script>
import { Button, Dropdown, DropdownMenu, DropdownItem } from '@statamic/cms/ui';

export default {
    components: {
        Button,
        Dropdown,
        DropdownMenu,
        DropdownItem,
    },

    props: {
        title: {
            type: String,
            required: true,
        },
        fieldActions: {
            type: Array,
            default: () => [],
        },
    },
};
</script>
