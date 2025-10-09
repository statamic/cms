<template>
    <div class="flex">
        <slot v-if="!hasMultipleBlueprints" name="trigger" :create="create">
            <Button @click="create" :variant :text="text" :size="size" :icon="icon" />
        </slot>
        <Dropdown v-else>
            <template #trigger>
                <slot name="trigger" :create="create">
                    <Button @click.prevent="create" :variant icon-append="chevron-down" :text="text" :size="size" :icon="icon" />
                </slot>
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
import { router } from '@inertiajs/vue3';

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
        icon: { type: String, default: null },
        url: { type: String },
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

            $event.metaKey ? window.open(url) : router.get(url);
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
