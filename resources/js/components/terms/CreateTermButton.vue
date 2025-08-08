<template>
    <div>
        <Button @click="create" v-if="!hasMultipleBlueprints" :variant :text="text" :size="size" />
        <Dropdown v-else>
            <template #trigger>
                <Button @click.prevent="create" :variant icon-append="ui/chevron-down" :text="text" :size="size" />
            </template>
            <DropdownMenu>
                <DropdownLabel v-text="__('Choose Blueprint')" />
                <DropdownItem
                    v-for="blueprint in blueprints"
                    :key="blueprint.handle"
                    @click="select(blueprint.handle, $event)"
                    :text="blueprint.title"
                />
            </DropdownMenu>
        </Dropdown>
    </div>
</template>

<script>
import { Button, Dropdown, DropdownMenu, DropdownItem, DropdownLabel } from '@statamic/ui';

export default {
    components: {
        Button,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        DropdownLabel,
    },

    props: {
        url: String,
        blueprints: Array,
        variant: { type: String, default: 'primary' },
        text: { type: String, default: () => __('Create Term') },
        size: { type: String, default: 'base' },
        buttonClass: { type: String, default: 'btn' },
    },

    computed: {
        hasMultipleBlueprints() {
            return this.blueprints.length > 1;
        },
    },

    methods: {
        create($event) {
            if (this.blueprints.length === 1) this.select(null, $event);
        },

        select(blueprint, $event) {
            let url = this.url;

            if (blueprint) {
                url = url += `?blueprint=${blueprint}`;
            }

            $event.metaKey ? window.open(url) : (window.location = url);
        },
    },
};
</script>
