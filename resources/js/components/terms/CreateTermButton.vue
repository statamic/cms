<template>

    <dropdown-list class="inline-block" :disabled="blueprints.length === 1">
        <template v-slot:trigger>
            <button
                class="btn-primary flex items-center"
                @click="create"
            >
                <span v-text="__('Create Term')" />
                <svg-icon name="chevron-down-xs" class="ml-1 -mr-1 w-2" v-if="blueprints.length > 1" />
            </button>
        </template>

        <div v-for="blueprint in blueprints" :key="blueprint.handle">
            <dropdown-item :text="blueprint.title" @click="select(blueprint.handle)" />
        </div>
    </dropdown-list>

</template>

<script>
export default {

    props: {
        url: String,
        blueprints: Array
    },

    methods: {

        create() {
            if (this.blueprints.length === 1) this.select();
        },

        select(blueprint) {
            let url = this.url;

            if (blueprint) {
                url = url += `?blueprint=${blueprint}`;
            }

            window.location = url;
        }

    }

}
</script>
