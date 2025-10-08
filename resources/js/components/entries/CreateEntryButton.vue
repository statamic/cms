<template>
    <div class="flex">
        <template v-if="!hasMultipleBlueprints">
            <slot name="trigger" :create="create" v-if="customTrigger">
                <Button @click="create" :variant :text="text" :size="size" :icon="icon" />
            </slot>
            <Button v-else @click="create" :variant :text="text" :size="size" :icon="icon" />
        </template>
        <Dropdown v-else>
            <template #trigger>
                <slot name="trigger" :create="create" v-if="customTrigger">
                    <Button @click.prevent="create" :variant icon-append="chevron-down" :text="text" :size="size" :icon="icon" />
                </slot>
                <Button v-else @click.prevent="create" :variant icon-append="chevron-down" :text="text" :size="size" :icon="icon" />
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
        blueprints: {type: Array },
        buttonClass: { type: String, default: 'btn' },
        commandPalette: { type: Boolean, default: false },
        icon: { type: String, default: null },
        size: { type: String, default: 'base' },
        text: { type: String, default: () => __('Create Entry') },
        url: { type: String },
        variant: { type: String, default: 'primary' },
        customTrigger: { type: Boolean, default: false },
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
