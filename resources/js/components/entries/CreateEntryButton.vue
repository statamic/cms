<template>
    <div>
        <ui-button @click="create" v-if="!hasMultipleBlueprints" :text="text" />
        <ui-dropdown v-else>
            <template #trigger>
                <ui-button @click.prevent="create" :variant icon-append="ui/chevron-down" :text="text" />
            </template>
            <ui-dropdown-menu>
                <ui-dropdown-label v-text="__('Choose Blueprint')" />
                <ui-dropdown-item
                    v-for="blueprint in blueprints"
                    :key="blueprint.handle"
                    @click="select(blueprint.handle, $event)"
                    :text="blueprint.title"
                />
            </ui-dropdown-menu>
        </ui-dropdown>
    </div>
</template>

<script>
export default {
    props: {
        url: String,
        blueprints: Array,
        variant: { type: String, default: 'primary' },
        text: { type: String, default: () => __('Create Entry') },
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
