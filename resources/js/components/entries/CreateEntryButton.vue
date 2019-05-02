<template>

    <dropdown-list class="inline-block">
        <button
            slot="trigger"
            class="btn btn-primary"
            @click="create"
            v-text="__('Create Entry')"
        />
        <ul class="dropdown-menu" v-if="blueprints.length > 1">
            <li v-for="blueprint in blueprints" :key="blueprint.handle">
                <a @click="select(blueprint.handle)" v-text="blueprint.title" />
            </li>
        </ul>
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
