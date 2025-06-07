<template>
    <header class="fixed inset-x-0 top-0 z-max flex items-center justify-between bg-white/90 px-4 shadow dark:bg-dark-550">
        <h2 class="shrink-0" v-text="__(title)" />
        <div class="grow-1 flex min-w-max items-center gap-4">
            <slot />
        </div>
        <div class="flex w-full items-center justify-end py-2.5">
            <Dropdown class="mr-2">
                <template #trigger>
                    <Button icon="ui/dots" variant="ghost" size="xs" />
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
                <svg-icon :name="action.icon" class="h-4 w-4" />
            </button>
        </div>
    </header>
</template>

<script>
import { Button, Dropdown, DropdownMenu, DropdownItem } from '@statamic/ui';

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
