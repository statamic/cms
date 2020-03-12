<template>
    <div v-if="presets" class="border-b px-2 text-sm">

        <button class="data-list-filter-link" :class="{ active: ! active }" @click="viewAll">All</button>

        <template v-for="(preset, handle) in presets">
            <button class="data-list-filter-link" :class="{ active: handle === active }" @click="viewPreset(handle)">
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
    },

    methods: {
        getPresets() {
            this.presets = this.$preferences.get(this.preferencesKey);
        },

        setPreset(handle) {
            this.getPresets();
            this.viewPreset(handle)
        },

        refreshPresets() {
            this.getPresets();
            this.viewAll();
        },

        viewAll() {
            this.active = null;

            this.$emit('reset');
        },

        viewPreset(handle) {
            this.active = handle;

            this.$emit('selected', handle, this.presets[handle]);
        },
    },

}
</script>
