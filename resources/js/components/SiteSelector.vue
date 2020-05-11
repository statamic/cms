<template>

    <v-select
        class="text-sm"
        :value="site"
        :clearable="false"
        :searchable="false"
        :get-option-label="site => site.name"
        :options="sites"
        @input="$emit('input', $event)"
    />

</template>

<script>
export default {

    props: {
        sites: { type: Array, required: true },
        value: { type: String, required: true },
    },

    computed: {

        site() {
            return _.findWhere(this.sites, { handle: this.value });
        }

    }

}
</script>
