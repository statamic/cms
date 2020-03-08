<template>

    <dropdown-list :disabled="blueprints.length <= 1">
        <template v-slot:trigger>
            <button
                :class="buttonClass"
                @click="create"
                v-text="text" />
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
        blueprints: Array,
        text: { type: String, default: () => __('Create Entry') },
        buttonClass: { type: String, default: 'btn' }
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
