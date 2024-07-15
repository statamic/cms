<template>

    <v-select
        class="text-sm"
        :clearable="false"
        :searchable="false"
        :get-option-label="site => __(site.name)"
        :options="sites"
        :model-value="site"
        @update:model-value="$emit('update:model-value', $event)"
    />

</template>

<script>
export default {

    props: {
        sites: { type: Array, required: true },
        modelValue: { type: String, required: true },
    },

    computed: {

        site() {
            return _.findWhere(this.sites, { handle: this.modelValue });
        }

    }

}
</script>
