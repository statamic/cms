<template>
    <div v-if="presets" class="border-b px-2 text-sm">

        <button class="data-list-filter-link" :class="{ active: ! active }" @click="viewAll">All</button>

        <template v-for="(preset, slug) in presets">
            <button class="data-list-filter-link" :class="{ active: slug === active }" @click="viewPreset(slug)">
                {{ preset.display }}
            </button>
        </template>

    </div>
</template>

<script>
export default {

    props: {
        preferencesPrefix: String,
    },

    data() {
        return {
            active: null,
            presets: [],
        }
    },

    computed: {
        preferencesKey() {
            return this.preferencesPrefix ? `${this.preferencesPrefix}.filters` : null;
        },
    },

    created() {
        if (this.preferencesKey) {
            this.getPresets();
        }

        this.$events.$on('filter-preset-saved', this.setPreset);
    },

    methods: {
        getPresets() {
            this.presets = this.$preferences.get(this.preferencesKey);
        },

        setPreset(slug) {
            this.active = slug;

            this.getPresets();
        },

        viewAll() {
            this.active = null;

            this.$emit('reset');
        },

        viewPreset(slug) {
            this.active = slug;

            this.$emit('selected', this.presets[slug]);
        },
    },

}
</script>
