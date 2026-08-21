<script setup>
import PresetTrigger from './PresetTrigger.vue';
import {
    Button,
    Input,
    Dropdown,
    DropdownItem,
    DropdownMenu,
    DropdownSeparator,
    Tabs,
    TabList,
} from '@ui';
import { injectListingContext } from '../Listing/Listing.vue';
import { computed, ref, watch } from 'vue';
import { deepClone } from '@/util/clone.js';

const { preferencesPrefix, activeFilters, searchQuery, setFilters, clearFilters, setSearchQuery, clearSearchQuery } =
    injectListingContext();
const preferencesKey = ref(`${preferencesPrefix.value}.filters`);
const presets = ref(getPresets());
const activePreset = ref(getPresetFromActiveFilters());
const activePresetPayload = computed(() => presets.value[activePreset.value]);
const selectedPreset = ref(activePreset.value);
const selectedPresetPayload = computed(() => presets.value[selectedPreset.value]);
const savingPresetName = ref(null);
const savingPresetHandle = computed(() => snake_case(savingPresetName.value));
const isCreating = ref(false);
const isRenaming = ref(false);
const isConfirmingDeletion = ref(false);

watch(
    [activeFilters, searchQuery],
    () => {
        activePreset.value = getPresetFromActiveFilters();
        selectedPreset.value ??= activePreset.value;
    },
    { deep: true },
);

function getPresets() {
    return Statamic.$preferences.get(preferencesKey.value, {});
}

function refreshPresets() {
    presets.value = getPresets();
}

function viewAll() {
    activePreset.value = null;
    selectedPreset.value = null;
    clearFilters();
    clearSearchQuery();
}

function selectPreset(handle) {
    activePreset.value = handle;
    selectedPreset.value = handle;
    setFilters(deepClone(activePresetPayload.value.filters ?? {}));
    setSearchQuery(activePresetPayload.value.query);
}

function createPreset() {
    savingPresetName.value = null;
    isCreating.value = true;
}

function canSavePreset(handle) {
    return !Statamic.$preferences.hasDefault(`${preferencesKey.value}.${handle}`);
}

function canDeletePreset(handle) {
    return canSavePreset(handle);
}

function renamePreset() {
    savingPresetName.value = selectedPresetPayload.value.display;
    isRenaming.value = true;
}

const canSaveNewPreset = computed(() => {
    if (selectedPreset.value) return isDirty.value;

    return (Object.keys(activeFilters.value).length > 0 || (searchQuery.value ?? '') !== '');
});

const isDirty = computed(() => {
    if (!selectedPreset.value || !selectedPresetPayload.value) return false;

    const savedFilters = selectedPresetPayload.value.filters ?? {};
    const savedQuery = selectedPresetPayload.value.query ?? '';
    const currentQuery = searchQuery.value ?? '';

    if (savedQuery !== currentQuery) return true;

    const savedKeys = Object.keys(savedFilters);
    const currentKeys = Object.keys(activeFilters.value);

    if (savedKeys.length !== currentKeys.length) return true;

    for (const key of savedKeys) {
        if (JSON.stringify(savedFilters[key]) !== JSON.stringify(activeFilters.value[key])) {
            return true;
        }
    }

    return false;
});

function savePreset() {
    const payload = { display: selectedPresetPayload.value.display };

    if (searchQuery.value) payload.query = searchQuery.value;
    if (Object.entries(activeFilters.value).length) payload.filters = deepClone(activeFilters.value);

    Statamic.$preferences
        .set(`${preferencesKey.value}.${selectedPreset.value}`, payload)
        .then(() => {
            Statamic.$toast.success(__('View saved'));
            refreshPresets();
        })
        .catch(() => {
            Statamic.$toast.error(__('Unable to save view'));
        });
}

function resetPreset() {
    setFilters(deepClone(selectedPresetPayload.value.filters ?? {}));
    setSearchQuery(selectedPresetPayload.value.query ?? '');
}

function getPresetFromActiveFilters() {
    for (const [handle, preset] of Object.entries(presets.value)) {
        const a = {
            filters: preset.filters ?? {},
            query: preset.query ?? '',
        };

        const b = {
            filters: activeFilters.value,
            query: searchQuery.value ?? '',
        };

        if (JSON.stringify(a) === JSON.stringify(b)) {
            return handle;
        }
    }
}

const currentTab = computed({
    get: () => selectedPreset.value || 'all',
    set: (value) => {
        if (value === 'all') {
            viewAll();
        } else {
            selectPreset(value);
        }
    },
});

const presetPreferencesPayload = computed(() => {
    let payload = {
        display: savingPresetName.value || selectedPresetPayload.value?.display || '',
    };

    if (searchQuery.value) payload.query = searchQuery.value;
    if (Object.entries(activeFilters.value).length) payload.filters = activeFilters.value;

    return payload;
});

function saveNew() {
    const handle = savingPresetHandle.value || activePreset.value;

    Statamic.$preferences
        .set(`${preferencesKey.value}.${handle}`, presetPreferencesPayload.value)
        .then((response) => {
            Statamic.$toast.success(__('View saved'));
            isCreating.value = false;
            savingPresetName.value = null;
            refreshPresets();
            selectPreset(handle);
        })
        .catch((error) => {
            Statamic.$toast.error(__('Unable to save view'));
            isCreating.value = false;
            savingPresetName.value = null;
        });
}

function saveExisting() {
    let preference = Statamic.$preferences.get(`${preferencesKey.value}`);

    preference = Object.fromEntries(
        Object.entries(preference).map(([key, value]) => {
            if (key === selectedPreset.value) {
                return [savingPresetHandle.value, presetPreferencesPayload.value];
            }

            return [key, value];
        }),
    );

    Statamic.$preferences
        .set(`${preferencesKey.value}`, preference)
        .then((response) => {
            Statamic.$toast.success(__('View renamed'));
            isRenaming.value = false;
            refreshPresets();
            selectPreset(savingPresetHandle.value);
        })
        .catch((error) => {
            Statamic.$toast.error(__('Unable to rename view'));
            isRenaming.value = false;
        });
}

function deletePreset() {
    Statamic.$preferences
        .remove(`${preferencesKey.value}.${selectedPreset.value}`)
        .then((response) => {
            Statamic.$toast.success(__('View deleted'));
            isConfirmingDeletion.value = false;
            viewAll();
            refreshPresets();
        })
        .catch((error) => {
            Statamic.$toast.error(__('Unable to delete view'));
            isConfirmingDeletion.value = false;
        });
}
</script>

<template>
    <Tabs v-model:modelValue="currentTab">
        <div class="relative flex shrink-0 items-center space-x-2.5 px-2 -mt-2 sm:px-0 starting-style-transition">
            <TabList class="flex-1 space-x-2.5">
                <PresetTrigger name="all" :text="__('All')" />
                <PresetTrigger
                    v-for="(preset, handle) in presets"
                    :key="handle"
                    :name="handle"
                >
                    {{ preset.display }}
                    <template v-if="handle === selectedPreset">
                        <Dropdown class="w-48!">
                            <template #trigger>
                                <Button class="absolute! top-0.25 -right-4 starting-style-transition starting-style-transition--slow" variant="ghost" size="xs" icon="chevron-down" />
                            </template>
                            <DropdownMenu>
                                <DropdownItem :text="__('Duplicate')" icon="duplicate" @click="createPreset" />
                                <DropdownItem
                                    v-if="canSavePreset(handle)"
                                    :text="__('Rename')"
                                    icon="rename"
                                    @click="renamePreset"
                                />
                                <DropdownSeparator v-if="canDeletePreset(handle)" />
                                <DropdownItem
                                    v-if="canDeletePreset(handle)"
                                    :text="__('Delete')"
                                    icon="delete"
                                    variant="destructive"
                                    @click="isConfirmingDeletion = true"
                                />
                            </DropdownMenu>
                        </Dropdown>
                    </template>
                </PresetTrigger>
            </TabList>
            <div v-if="isDirty || canSaveNewPreset" class="border-b border-gray-200 dark:border-gray-700 relative -top-[2px] hover:border-transparent ps-2 flex gap-1">
                <Button
                    v-if="isDirty && canSavePreset(selectedPreset)"
                    @click="savePreset"
                    variant="ghost"
                    size="sm"
                    :text="__('Save')"
                    icon="save"
                    class="[&_svg]:size-4"
                />
                <Button
                    v-if="isDirty"
                    @click="resetPreset"
                    variant="ghost"
                    size="sm"
                    :text="__('Reset')"
                    icon="sync"
                    class="[&_svg]:size-4"
                />
                <Button
                    v-if="canSaveNewPreset"
                    @click="createPreset"
                    variant="ghost"
                    size="sm"
                    :text="__('New View')"
                    icon="add-bookmark"
                    class="[&_svg]:size-4"
                />
            </div>
        </div>
    </Tabs>

    <confirmation-modal
        :open="isCreating"
        :title="__('Create New View')"
        :buttonText="__('Create')"
        @cancel="isCreating = false"
        @confirm="saveNew"
    >
        <Input focus v-model="savingPresetName" @keydown.enter="saveNew" />

        <ui-error-message
            v-if="presets && Object.keys(presets).includes(savingPresetHandle)"
            :text="__('messages.filters_view_already_exists')"
        />
    </confirmation-modal>

    <confirmation-modal
        :open="isRenaming"
        :title="__('Rename View')"
        :buttonText="__('Rename')"
        @cancel="isRenaming = false"
        @confirm="saveExisting"
    >
        <Input focus v-model="savingPresetName" @keydown.enter="saveExisting" />

        <div
            v-if="
                Object.keys(presets)
                    .filter((preset) => preset !== selectedPreset)
                    .includes(savingPresetHandle)
            "
        >
            <ui-error-message :text="__('messages.filters_view_already_exists')" />
        </div>
    </confirmation-modal>

    <confirmation-modal
        :open="isConfirmingDeletion"
        :title="__('Delete View')"
        :bodyText="__('Are you sure you want to delete this view?')"
        :buttonText="__('Delete')"
        danger
        @confirm="deletePreset"
        @cancel="isConfirmingDeletion = false"
    />
</template>
