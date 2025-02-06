<template>
    <div class="pt-2 ltr:pr-2 rtl:pl-2">
        <div class="flex flex-wrap items-center">
            <button
                class="pill-tab ltr:mr-1 rtl:ml-1"
                :class="{ active: !activePreset }"
                @click="viewAll"
                v-text="__('All')"
            />

            <template v-for="(preset, handle) in presets">
                <button class="pill-tab active ltr:mr-1 rtl:ml-1" v-if="handle === activePreset">
                    {{ preset.display }}
                    <dropdown-list class="ltr:ml-2 rtl:mr-2" placement="bottom-start">
                        <template v-slot:trigger>
                            <button class="opacity-50 hover:opacity-100">
                                <svg-icon name="micro/chevron-down-xs" class="h-2 w-2" />
                            </button>
                        </template>
                        <dropdown-item :text="__('Duplicate')" @click="createPreset" />
                        <dropdown-item v-if="canRenamePreset(handle)" :text="__('Rename')" @click="renamePreset" />
                        <div class="divider" />
                        <dropdown-item
                            v-if="canDeletePreset(handle)"
                            :text="__('Delete')"
                            class="warning"
                            @click="showDeleteModal = true"
                        />
                    </dropdown-list>
                </button>
                <button class="pill-tab ltr:mr-1 rtl:ml-1" v-else @click="viewPreset(handle)">
                    {{ preset.display }}
                </button>
            </template>

            <button class="pill-tab" @click="createPreset" v-tooltip="__('Create New View')">
                <svg-icon name="add" class="h-3 w-3" />
            </button>
        </div>

        <confirmation-modal
            v-if="showCreateModal"
            :title="__('Create New View')"
            :buttonText="__('Create')"
            @cancel="showCreateModal = false"
            @confirm="savePreset(savingPresetSlug)"
        >
            <text-input :focus="true" v-model="savingPresetName" @keydown.enter="savePreset(savingPresetSlug)" />

            <div v-if="presets && Object.keys(presets).includes(savingPresetSlug)">
                <small
                    class="help-block mb-0 mt-2 text-red-500"
                    v-text="__('messages.filters_view_already_exists')"
                ></small>
            </div>
        </confirmation-modal>

        <confirmation-modal
            v-if="showRenameModal"
            :title="__('Rename View')"
            :buttonText="__('Rename')"
            @cancel="showRenameModal = false"
            @confirm="savePreset(savingPresetSlug)"
        >
            <text-input :focus="true" v-model="savingPresetName" @keydown.enter="savePreset(savingPresetSlug)" />

            <div
                v-if="
                    Object.keys(presets)
                        .filter((preset) => preset !== activePreset)
                        .includes(savingPresetSlug)
                "
            >
                <small
                    class="help-block mb-0 mt-2 text-red-500"
                    v-text="__('messages.filters_view_already_exists')"
                ></small>
            </div>
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
            showCreateModal: false,
            showRenameModal: false,
            showDeleteModal: false,
            savingPresetName: null,
            test: 'hello!',
        };
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

        savingPresetSlug() {
            return snake_case(this.savingPresetName);
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
            this.viewPreset(handle);
        },

        refreshPresets() {
            this.getPresets();
            this.viewAll();
        },

        refreshPreset() {
            if (this.activePreset) {
                this.setPreset(this.activePreset);
            } else {
                this.viewAll();
            }
        },

        canRenamePreset(handle) {
            return !this.$preferences.hasDefault(`${this.preferencesKey}.${handle}`);
        },

        canDeletePreset(handle) {
            return !this.$preferences.hasDefault(`${this.preferencesKey}.${handle}`);
        },

        viewAll() {
            this.$emit('reset');
        },

        viewPreset(handle) {
            this.$emit('selected', handle, this.presets[handle]);
        },

        createPreset() {
            this.savingPresetName = null;
            this.showCreateModal = true;
        },

        renamePreset() {
            this.savingPresetName = this.activePresetPayload.display;
            this.showRenameModal = true;
        },

        savePreset(handle) {
            let presetHandle = handle || this.activePreset;

            if (!presetHandle) {
                this.showCreateModal = true;
                return;
            }

            if (this.showRenameModal) {
                let preference = this.$preferences.get(`${this.preferencesKey}`);

                preference = Object.fromEntries(
                    Object.entries(preference).map(([key, value]) => {
                        if (key === this.activePreset) {
                            return [this.savingPresetSlug, this.presetPreferencesPayload];
                        }

                        return [key, value];
                    }),
                );

                this.$preferences
                    .set(`${this.preferencesKey}`, preference)
                    .then((response) => {
                        this.$toast.success(__('View renamed'));
                        this.$emit('deleted', this.activePreset);
                        this.showRenameModal = false;
                        this.refreshPresets();
                    })
                    .catch((error) => {
                        this.$toast.error(__('Unable to rename view'));
                        this.showRenameModal = false;
                    });

                return;
            }

            this.$preferences
                .set(`${this.preferencesKey}.${presetHandle}`, this.presetPreferencesPayload)
                .then((response) => {
                    this.$toast.success(__('View saved'));
                    this.showCreateModal = false;
                    this.savingPresetName = null;
                    this.setPreset(presetHandle);
                })
                .catch((error) => {
                    this.$toast.error(__('Unable to save view'));
                    this.showCreateModal = false;
                    this.showRenameModal = false;
                    this.savingPresetName = null;
                });
        },

        deletePreset() {
            this.$preferences
                .remove(`${this.preferencesKey}.${this.activePreset}`)
                .then((response) => {
                    this.$emit('deleted', this.activePreset);
                    this.$toast.success(__('View deleted'));
                    this.showDeleteModal = false;
                    this.refreshPresets();
                })
                .catch((error) => {
                    this.$toast.error(__('Unable to delete view'));
                    this.showDeleteModal = false;
                });
        },
    },
};
</script>
