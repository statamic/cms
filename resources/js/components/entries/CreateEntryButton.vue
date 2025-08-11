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
        text: { type: String, default: () => __('Create Entry') },
        size: { type: String, default: 'base' },
        buttonClass: { type: String, default: 'btn' },
        commandPalette: { type: Boolean, default: false },
    },

    computed: {
        hasMultipleBlueprints() {
            return this.blueprints.length > 1;
        },
    },

    mounted() {
        this.addToCommandPalette();
    },

    methods: {
        create($event) {
            if (this.blueprints.length === 1) this.select(null, $event);
        },

        select(blueprint, $event) {
            let url = this.createUrl(blueprint);

            $event.metaKey ? window.open(url) : (window.location = url);
        },

        createUrl(blueprint) {
            let url = this.url;

            if (blueprint) {
                url = url += `?blueprint=${blueprint}`;
            }

            return url;
        },

        addToCommandPalette() {
            if (! this.commandPalette) {
                return;
            }

            let title = __('Create Entry');

            this.blueprints.forEach(blueprint => {
                Statamic.$commandPalette.add({
                    category: Statamic.$commandPalette.category.Actions,
                    text: this.hasMultipleBlueprints ? [title, blueprint.title] : title,
                    icon: 'entry',
                    url: this.createUrl(blueprint.handle),
                    prioritize: true,
                });
            });
        },
    },
};
</script>
