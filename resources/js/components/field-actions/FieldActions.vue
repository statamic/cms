<template>
    <div class="flex relative items-center gap-1 -top-1">
        <Dropdown v-if="hasNonQuickActions">
            <template #trigger>
                <Button icon="dots" variant="ghost" size="xs" :aria-label="__('Open dropdown menu')" />
            </template>
            <DropdownMenu>
                <DropdownItem
                    v-for="action in actions.filter((a) => !a.quick)"
                    :key="action.handle || action.title"
                    :text="action.title"
                    :variant="action.dangerous ? 'destructive' : 'default'"
                    :aria-label="action.title"
                    @click="action.run(action)"
                />
            </DropdownMenu>
        </Dropdown>
        <ButtonGroup class="mr-0.75 -mt-0.5">
            <Button
                v-for="(action, index) in actions.filter((a) => a.quick)"
                :key="index"
                @click="action.run()"
                v-tooltip="action.title"
                size="2xs"
                :disabled="action.disabled"
                :icon-only="true"
                :aria-label="action.title"
            >
                <ui-icon :name="action.icon" class="size-3.5" />
            </Button>
        </ButtonGroup>
    </div>
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
        actions: {
            type: Array,
        },
    },

    computed: {
        hasQuickActions() {
            return this.actions.filter((a) => a.quick).length > 0;
        },

        hasNonQuickActions() {
            return this.actions.filter((a) => !a.quick).length > 0;
        },
    },
};
</script>
