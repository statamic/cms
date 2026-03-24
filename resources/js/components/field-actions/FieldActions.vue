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
            <!-- Keep quick actions focusable for :focus-within styles, even when disabled. -->
            <Button
                v-for="(action, index) in actions.filter((a) => a.quick)"
                :key="index"
                @click="runQuickAction(action, $event)"
                v-tooltip="action.title"
                size="2xs"
                :aria-disabled="action.disabled ? 'true' : null"
                :class="{ '!cursor-not-allowed': action.disabled }"
                :icon-only="true"
                :aria-label="action.title"
            >
                <ui-icon :name="action.icon" class="size-3.5" :class="{ '!opacity-30': action.disabled }" />
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

    methods: {
        runQuickAction(action, event) {
            const target = event?.currentTarget;

            if (action.disabled) {
                if (target instanceof HTMLButtonElement) {
                    // Re-focus after click so selection/focus styling doesn't jump to parent containers.
                    requestAnimationFrame(() => target.focus({ preventScroll: true }));
                }
                return;
            }

            action.run();
        },
    },
};
</script>
