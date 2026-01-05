<template>
    <header class="fixed h-12 inset-x-0 top-0 z-max flex items-center justify-between bg-gray-50 dark:bg-gray-950 px-4 shadow-ui-lg dark:shadow-none dark:border-b dark:border-white/10">
        <ui-heading class="shrink-0" :text="__(title)" />
        <div class="flex min-w-max items-center gap-4">
            <slot />
        </div>
        <div class="flex items-center justify-end gap-2 py-2.5">
            <Dropdown v-if="hasNonQuickActions" class="mr-2">
                <template #trigger>
                    <Button icon="dots" variant="ghost" size="xs" :aria-label="__('Open dropdown menu')" />
                </template>
                <DropdownMenu>
                    <DropdownItem
                        v-if="fieldActions.length"
                        v-for="action in fieldActions.filter((a) => !a.quick)"
                        :text="action.title"
                        :variant="action.dangerous ? 'destructive' : 'default'"
                        @click="action.run(action)"
                    />
                </DropdownMenu>
            </Dropdown>
            <ButtonGroup>
                <Button
                    v-for="(action, index) in fieldActions.filter((a) => a.quick)"
                    :key="index"
                    v-tooltip="action.title"
                    @click="action.run()"
                    size="xs"
                    :disabled="action.disabled"
                    :icon="action.icon"
                    :aria-label="action.title"
                    tabindex="-1"
                />
            </ButtonGroup>
        </div>
    </header>
</template>

<script>
import { Button, ButtonGroup, Dropdown, DropdownMenu, DropdownItem } from '@/components/ui';

export default {
    components: {
        Button,
        ButtonGroup,
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

    computed: {
        hasNonQuickActions() {
            return this.fieldActions.filter((a) => !a.quick).length > 0;
        },
    }
};
</script>
