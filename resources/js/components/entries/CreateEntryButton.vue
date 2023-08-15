<template>

    <dropdown-list class="inline-block" :disabled="!hasMultipleBlueprints">
        <template v-slot:trigger>
            <button
                :class="[buttonClass, {'flex items-center pr-4': hasMultipleBlueprints }]"
                @click="create"
            >
                {{ text }}
                <svg-icon name="micro/chevron-down-xs" class="w-2 ml-2" v-if="hasMultipleBlueprints" />
            </button>
        </template>
        <h6 v-text="__('Choose Blueprint')" class="p-2" />

        <div v-for="blueprint in blueprints" :key="blueprint.handle">
            <dropdown-item :text="blueprint.title" @click="select(blueprint.handle, $event)" />
        </div>
    </dropdown-list>

</template>

<script>
export default {

    props: {
        url: String,
        blueprints: Array,
        text: { type: String, default: () => __('Create Entry') },
        buttonClass: { type: String, default: 'btn' }
    },

    computed: {

        hasMultipleBlueprints() {
            return this.blueprints.length > 1;
        }

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

            $event.metaKey ? window.open(url) : window.location = url;
        }

    }

}
</script>
