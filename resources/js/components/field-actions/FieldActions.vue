<template>
    <div class="field-dropdown">
        <div class="quick-list">
            <div class="quick-list-content">
                <Button
                    v-for="(action, index) in actions.filter((a) => a.quick)"
                    :key="index"
                    @click="action.run()"
                    v-tooltip="action.title"
                    :icon-only="true"
                    size="xs"
                    variant="ghost"
                >
                    <svg-icon :name="action.icon" class="size-3" />
                </Button>
            </div>
            <Dropdown>
                <template #trigger>
                    <Button icon="ui/dots" variant="ghost" size="xs" />
                </template>
                <DropdownMenu>
                    <DropdownItem
                        v-for="action in actions"
                        :text="action.title"
                        :variant="action.dangerous ? 'destructive' : 'default'"
                        @click="action.run(action)"
                    />
                </DropdownMenu>
            </Dropdown>
        </div>
    </div>
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
        actions: {
            type: Array,
        },
    },
};
</script>
