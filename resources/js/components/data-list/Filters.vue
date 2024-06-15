<template>
    <div class="shadow-inner bg-gray-300 dark:bg-dark-600">
        <div class="flex items-center flex-wrap px-3 border-b dark:border-dark-900 pt-2">

            <!-- Field filter (requires custom selection UI) -->
            <popover v-if="fieldFilter" placement="bottom-start" @closed="fieldFilterClosed">
                <template #trigger>
                    <button class="filter-badge filter-badge-control rtl:ml-2 ltr:mr-2 mb-2" @click="resetFilterPopover">
                        {{ fieldFilter.title }}
                        <svg-icon name="micro/chevron-down-xs" class="w-2 h-2 mx-2" />
                    </button>
                </template>
                <template #default="{ close: closePopover }">
                    <div class="flex flex-col rtl:text-right ltr:text-left min-w-[18rem]">
                        <div class="filter-fields text-sm">
                            <field-filter
                                ref="fieldFilter"
                                :config="fieldFilter"
                                :values="activeFilters.fields || {}"
                                :badges="fieldFilterBadges"
                                @changed="$emit('changed', { handle: 'fields', values: $event })"
                                @cleared="creating = false"
                                @closed="closePopover"
                            />
                        </div>
                    </div>
                </template>
            </popover>

            <!-- Standard pinned filters -->
            <popover v-if="pinnedFilters.length" v-for="filter in pinnedFilters" :key="filter.handle" placement="bottom-start" :stop-propagation="false">
                <template #trigger>
                    <button class="filter-badge filter-badge-control rtl:ml-2 ltr:mr-2 mb-2">
                        {{ filter.title }}
                        <svg-icon name="micro/chevron-down-xs" class="w-2 h-2 mx-2" />
                    </button>
                </template>
                <template #default="{ close: closePopover }">
                    <div class="filter-fields w-64">
                        <data-list-filter
                            :key="filter.handle"
                            :filter="filter"
                            :values="activeFilters[filter.handle]"
                            @changed="$emit('changed', { handle: filter.handle, values: $event })"
                            @closed="closePopover"
                        />
                    </div>
                </template>
            </popover>

            <!-- Standard unpinned filters -->
            <popover v-if="unpinnedFilters.length" placement="bottom-start" :stop-propagation="false">
                <template #trigger>
                    <button class="filter-badge filter-badge-control rtl:ml-2 ltr:mr-2 mb-2" @click="resetFilterPopover">
                        {{ __('Filter') }}
                        <svg-icon name="micro/chevron-down-xs" class="w-2 h-2 mx-2" />
                    </button>
                </template>
                <template #default="{ close: closePopover }">
                    <div class="filter-fields w-64">
                        <h6 v-text="creatingFilterHeader" class="p-3 pb-0" />
                        <div v-if="showUnpinnedFilterSelection" class="p-3 pt-1">
                            <button
                                v-for="filter in unpinnedFilters"
                                :key="filter.handle"
                                v-text="filter.title"
                                class="btn w-full mt-1"
                                @click="creating = filter.handle"
                            />
                        </div>
                        <div v-else>
                            <data-list-filter
                                v-for="filter in unpinnedFilters"
                                v-if="creating === filter.handle"
                                :key="filter.handle"
                                :filter="filter"
                                :values="activeFilters[filter.handle]"
                                @changed="$emit('changed', { handle: filter.handle, values: $event })"
                                @cleared="creating = false"
                                @closed="closePopover"
                            />
                        </div>
                    </div>
                </template>
            </popover>

            <!-- Active filter badges -->
            <div class="filter-badge rtl:ml-2 ltr:mr-2 mb-2" v-for="(badge, handle) in fieldFilterBadges" :key="handle">
                <span>{{ badge }}</span>
                <button @click="removeFieldFilter(handle)" v-tooltip="__('Remove Filter')">&times;</button>
            </div>
            <div class="filter-badge rtl:ml-2 ltr:mr-2 mb-2" v-for="(badge, handle) in standardBadges" :key="handle">
                <span>{{ badge }}</span>
                <button @click="removeStandardFilter(handle)" v-tooltip="__('Remove Filter')">&times;</button>
            </div>

        </div>
    </div>
</template>

<script>
import { ref, computed, inject, watch } from 'vue';
import DataListFilter from './Filter.vue';
import FieldFilter from './FieldFilter.vue';

export default {
    components: {
        DataListFilter,
        FieldFilter,
    },

    props: {
        filters: {
            type: Array,
            default: () => [],
        },
        activePreset: String,
        activePresetPayload: Object,
        activeFilters: Object,
        activeFilterBadges: Object,
        activeCount: Number,
        searchQuery: String,
        savesPresets: Boolean,
        preferencesPrefix: String,
        isSearching: Boolean,
    },

    setup(props, { emit }) {
        const filtering = ref(false);
        const creating = ref(false);
        const saving = ref(false);
        const deleting = ref(false);
        const savingPresetName = ref(null);
        const presets = ref([]);
        const sharedState = inject('sharedState');

        watch(
            () => props.activePresetPayload,
            (preset) => {
                savingPresetName.value = preset.display || null;
            },
            { deep: true }
        );

        const fieldFilter = computed(() => {
            return props.filters.find((filter) => filter.handle === 'fields');
        });

        const standardFilters = computed(() => {
            return props.filters.filter((filter) => filter.handle !== 'fields');
        });

        const pinnedFilters = computed(() => {
            return standardFilters.value.filter((filter) => filter.pinned);
        });

        const unpinnedFilters = computed(() => {
            return standardFilters.value.filter((filter) => !filter.pinned);
        });

        const creatingFilter = computed(() => {
            return _.find(unpinnedFilters.value, (filter) => filter.handle === creating.value);
        });

        const creatingFilterHeader = computed(() => {
            let text = data_get(creatingFilter.value, 'title', 'Filter where');
            return __(text) + ':';
        });

        const showUnpinnedFilterSelection = computed(() => {
            return !creating.value;
        });

        const fieldFilterBadges = computed(() => {
            return data_get(props.activeFilterBadges, 'fields', {});
        });

        const standardBadges = computed(() => {
            return _.omit(props.activeFilterBadges, 'fields');
        });

        const isFiltering = computed(() => {
            return !_.isEmpty(props.activeFilters) || props.searchQuery || props.activePreset;
        });

        const isDirty = computed(() => {
            if (!isFiltering.value) return false;

            if (props.activePreset) {
                return (
                    props.activePresetPayload.query != props.searchQuery ||
                    !_.isEqual(props.activePresetPayload.filters || {}, props.activeFilters)
                );
            }

            return true;
        });

        const canSave = computed(() => {
            return props.savesPresets && isDirty.value && props.preferencesPrefix;
        });

        const savingPresetHandle = computed(() => {
            return snake_case(savingPresetName.value);
        });

        const isUpdatingPreset = computed(() => {
            return savingPresetHandle.value === props.activePreset;
        });

        const preferencesKey = computed(() => {
            let handle = savingPresetHandle.value || props.activePreset;

            if (!props.preferencesPrefix || !handle) return null;

            return `${props.preferencesPrefix}.filters.${handle}`;
        });

        const preferencesPayload = computed(() => {
            if (!savingPresetName.value) return null;

            let payload = {
                display: savingPresetName.value,
            };

            if (props.searchQuery) payload.query = props.searchQuery;
            if (props.activeCount) payload.filters = clone(props.activeFilters);

            return payload;
        });

        const resetFilterPopover = () => {
            creating.value = false;

            setTimeout(() => {
                if (this.$refs.fieldFilter) {
                    this.$refs.fieldFilter.resetInitialValues();
                }
            }, 100); // wait for popover to appear
        };

        const fieldFilterClosed = () => {
            if (this.$refs.fieldFilter) {
                this.$refs.fieldFilter.popoverClosed();
            }
        };

        const removeFieldFilter = (handle) => {
            let fields = clone(props.activeFilters.fields);
            delete fields[handle];
            emit('changed', { handle: 'fields', values: fields });
        };

        const removeStandardFilter = (handle) => {
            emit('changed', { handle: handle, values: null });
        };

        const save = () => {
            if (!canSave.value || !preferencesPayload.value) return;

            saving.value = true;

            this.$preferences
                .set(preferencesKey.value, preferencesPayload.value)
                .then((response) => {
                    if (this.$refs.savePopover) {
                        this.$refs.savePopover.close();
                    }
                    emit('saved', savingPresetHandle.value);
                    this.$toast.success(
                        isUpdatingPreset.value ? __('Filter preset updated') : __('Filter preset saved')
                    );
                    savingPresetName.value = null;
                    saving.value = false;
                })
                .catch((error) => {
                    this.$toast.error(
                        isUpdatingPreset.value ? __('Unable to update filter preset') : __('Unable to save filter preset')
                    );
                    saving.value = false;
                });
        };

        const remove = () => {
            this.$preferences
                .remove(preferencesKey.value)
                .then((response) => {
                    emit('deleted', props.activePreset);
                    this.$toast.success(__('Filter preset deleted'));
                    deleting.value = false;
                })
                .catch((error) => {
                    this.$toast.error(__('Unable to delete filter preset'));
                    deleting.value = false;
                });
        };

        return {
            filtering,
            creating,
            saving,
            deleting,
            savingPresetName,
            presets,
            sharedState,
            fieldFilter,
            standardFilters,
            pinnedFilters,
            unpinnedFilters,
            creatingFilter,
            creatingFilterHeader,
            showUnpinnedFilterSelection,
            fieldFilterBadges,
            standardBadges,
            isFiltering,
            isDirty,
            canSave,
            savingPresetHandle,
            isUpdatingPreset,
            preferencesKey,
            preferencesPayload,
            resetFilterPopover,
            fieldFilterClosed,
            removeFieldFilter,
            removeStandardFilter,
            save,
            remove,
        };
    },
};
</script>
