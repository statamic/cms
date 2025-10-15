<template>

    <dropdown-list class="inline-block" :disabled="blueprints.length === 1">
        <template v-slot:trigger>
            <button
                class="btn-primary flex items-center"
                @click="create"
            >
                <span v-text="text" />
                <svg-icon name="micro/chevron-down-xs" class="rtl:mr-2 ltr:ml-2 -mr-2 w-2" v-if="blueprints.length > 1" />
            </button>
        </template>

        <div class="max-h-[75vh] overflow-y-auto">
            <div v-for="blueprint in blueprints" :key="blueprint.handle">
                <dropdown-item :text="blueprint.title" @click="select(blueprint.handle)" />
            </div>
        </div>
    </dropdown-list>

</template>

<script>
export default {

    props: {
        url: String,
        blueprints: Array,
        text: { type: String, default: () => __('Create Term') },
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
