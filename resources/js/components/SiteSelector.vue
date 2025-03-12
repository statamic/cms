<template>
    <v-select
        class="text-sm"
        :model-value="site"
        :clearable="false"
        :searchable="false"
        :get-option-label="(site) => __(site.name)"
        :options="sites"
        @update:model-value="$emit('input', $event)"
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
            return this.sites.find((s) => s.handle === this.value);
        },
    },
};
</script>
