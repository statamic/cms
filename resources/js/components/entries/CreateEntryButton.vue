<template>
    <div class="flex">
        <Button @click="create" v-if="!hasMultipleBlueprints" :variant :text="text" :size="size" />
        <Dropdown v-else>
            <template #trigger>
                <Button @click.prevent="create" :variant icon-append="chevron-down" :text="text" :size="size" />
            </template>
            <DropdownMenu>
                <DropdownLabel v-text="__('Choose Blueprint')" />
                <DropdownItem
                    v-for="blueprint in blueprints"
                    :key="blueprint.handle"
                    @click="select(blueprint, $event)"
                    :text="blueprint.title"
                />
            </DropdownMenu>
        </Dropdown>
    </div>
</template>

<script>
import { Button, Dropdown, DropdownMenu, DropdownItem, DropdownLabel } from '@/components/ui';

export default {
    components: {
        Button,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        DropdownLabel,
    },

    props: {
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
            if (!blueprint) blueprint = this.blueprints[0];
            return blueprint.createEntryUrl;
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
                    url: this.createUrl(blueprint),
                    prioritize: true,
                });
            });
        },
    },
};
</script>
