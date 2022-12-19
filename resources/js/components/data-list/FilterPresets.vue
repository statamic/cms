<template>
    <div v-if="presets" class="border-b px-2 text-sm">

        <button class="data-list-filter-link" :class="{ active: ! activePreset }" @click="viewAll" v-text="__('All')" />

        <template v-for="(preset, handle) in presets">
            <button class="data-list-filter-link" :class="{ active: handle === activePreset }" @click="viewPreset(handle)">
                {{ preset.display }}
            </button>
        </template>

    </div>
</template>

<script>
export default {

    props: {
        activePreset: String,
        preferencesPrefix: String,
    },

    data() {
        return {
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
            this.$emit('reset');
        },

        viewPreset(handle) {
            this.$emit('selected', handle, this.presets[handle]);
        },
    },

}
</script>
