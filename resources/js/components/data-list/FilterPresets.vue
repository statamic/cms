<template>
    <div>
        <div class="flex flex-wrap items-center">

            <button class="pill-tab mr-1" :class="{ 'active': ! activePreset }" @click="viewAll" v-text="__('All')" />

            <template v-for="(preset, handle) in presets">
                <button class="pill-tab active mr-1" v-if="handle === activePreset">
                    {{ preset.display }}
                    <button class="opacity-50 hover:opacity-100" @click="showDeleteModal = true">
                        <span class="w-2 h-2 ml-2 mr-1">&times;</span>
                    </button>
                </button>
                <button class="pill-tab mr-1" v-else @click="viewPreset(handle)">
                    {{ preset.display }}
                </button>
            </template>

            <button class="pill-tab" @click="createNewEmptyPreset" >
                <svg-icon name="add-bold" class="w-3 h-3"/>
            </button>
        </div>

        <confirmation-modal
            :buttonText="__('Save')"
            :title="__('Create New View')"
            v-if="showSaveModal"
            @cancel="showSaveModal = false"
            @confirm="handleSavePreset"
        >
            <text-input :focus="true" v-model="savingPresetName" @keydown.enter="handleSavePreset" />
        </confirmation-modal>

        <confirmation-modal
            v-if="showDeleteModal"
            :title="__('Delete View')"
            :bodyText="__('Are you sure you want to delete this view?')"
            :buttonText="__('Delete')"
            :danger="true"
            @confirm="deletePreset"
            @cancel="showDeleteModal = false"
        />
    </div>
</template>

<script>
export default {

    props: {
        activeFilters: Object,
        activePreset: String,
        activePresetPayload: Object,
        hasActiveFilters: Boolean,
        preferencesPrefix: String,
        searchQuery: String,
    },

    data() {
        return {
            presets: [],
            showDeleteModal: false,
            showSaveModal: false,
            savingPresetName: null,
            test: 'hello!'
        }
    },

    computed: {
        preferencesKey() {
            return this.preferencesPrefix ? `${this.preferencesPrefix}.filters` : null;
        },

        presetPreferencesPayload() {
            let payload = {
                display: this.savingPresetName || this.activePresetPayload.display,
            };

            if (this.searchQuery) payload.query = this.searchQuery;
            if (this.hasActiveFilters) payload.filters = clone(this.activeFilters);

            return payload;
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

        deletePreset() {
            this.$preferences.remove(`${this.preferencesKey}.${this.activePreset}`)
                .then(response => {
                    this.$emit('deleted', this.activePreset);
                    this.$toast.success(__('View deleted'));
                    this.showDeleteModal = false;
                    this.refreshPresets();
                })
                .catch(error => {
                    this.$toast.error(__('Unable to delete view'));
                    this.showDeleteModal = false;
                });
        },

        savePreset() {
            if (!this.activePreset) {
                this.showSaveModal = true;
                return;
            }

            this.handleSavePreset();
        },

        createNewEmptyPreset() {
            this.savingPresetName = null;
            this.showSaveModal = true;
        },

        handleSavePreset() {
            let presetHandle = this.activePreset || this.$slugify(this.savingPresetName, '_');

            this.$preferences.set(`${this.preferencesKey}.${presetHandle}`, this.presetPreferencesPayload)
                .then(response => {
                    this.$toast.success(__('View saved'));
                    this.showSaveModal = false;
                    this.getPresets();
                    this.viewPreset(presetHandle);
                    this.hasActiveFilters ? this.refreshPreset() : this.$emit('show-filters');
                })
                .catch(error => {
                    this.$toast.error(__('Unable to save view'));
                    this.showSaveModal = false;
                });
        },

        refreshPresets() {
            this.getPresets();
            this.viewAll();
        },

        refreshPreset() {
            this.getPresets();
            this.$emit('hide-filters');
            this.viewPreset(this.activePreset);
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
