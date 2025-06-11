<template>
    <div>
        <div
            class="relative flex shrink-0 space-x-2 border-b border-gray-200 text-sm text-gray-400 dark:border-gray-700 dark:text-gray-500"
        >
            <FilterTrigger :active="!activePreset" @click="viewAll" :text="__('All')" />
            <template v-for="(preset, handle) in presets">
                <FilterTrigger v-if="handle === activePreset" :active="true">
                    {{ preset.display }}
                    <Dropdown class="w-48!">
                        <template #trigger>
                            <Button
                                class="absolute top-1.5 -right-3"
                                variant="ghost"
                                size="xs"
                                @click="viewPreset(handle)"
                                icon="ui/chevron-down"
                            />
                        </template>
                        <DropdownMenu>
                            <DropdownItem :text="__('Duplicate')" icon="duplicate" @click="createPreset" />
                            <DropdownItem
                                v-if="canRenamePreset(handle)"
                                :text="__('Rename')"
                                icon="rename"
                                @click="renamePreset"
                            />
                            <DropdownSeparator v-if="canDeletePreset(handle)" />
                            <DropdownItem
                                v-if="canDeletePreset(handle)"
                                :text="__('Delete')"
                                icon="delete"
                                variant="warning"
                                @click="showDeleteModal = true"
                            />
                        </DropdownMenu>
                    </Dropdown>
                </FilterTrigger>
                <FilterTrigger v-else @click="viewPreset(handle)">
                    {{ preset.display }}
                </FilterTrigger>
            </template>
            <Button
                @click="createPreset"
                variant="ghost"
                size="sm"
                :text="__('New View')"
                icon="add-bookmark"
                class="relative top-0.5 [&_svg]:size-4"
            />
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
                    class="help-block mt-2 mb-0 text-red-500"
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
                    class="help-block mt-2 mb-0 text-red-500"
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
import FilterTrigger from './FilterTrigger.vue';
import { Button, Dropdown, DropdownItem, DropdownMenu, DropdownSeparator } from '@statamic/ui';

export default {
    components: {
        FilterTrigger,
        Button,
        Dropdown,
        DropdownItem,
        DropdownMenu,
        DropdownSeparator,
    },

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
