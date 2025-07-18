<script setup>
import PresetTrigger from './PresetTrigger.vue';
import { Button, Input, Dropdown, DropdownItem, DropdownMenu, DropdownSeparator } from '@statamic/ui';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import { computed, ref, watch } from 'vue';

const { preferencesPrefix, activeFilters, searchQuery, setFilters, clearFilters, setSearchQuery, clearSearchQuery } =
    injectListingContext();
const preferencesKey = ref(`${preferencesPrefix.value}.filters`);
const presets = ref(getPresets());
const activePreset = ref(getPresetFromActiveFilters());
const activePresetPayload = computed(() => presets.value[activePreset.value]);
const savingPresetName = ref(null);
const savingPresetHandle = computed(() => snake_case(savingPresetName.value));
const isCreating = ref(false);
const isRenaming = ref(false);
const isConfirmingDeletion = ref(false);

watch([activeFilters, searchQuery], () => (activePreset.value = getPresetFromActiveFilters()), { deep: true });

function getPresets() {
    return Statamic.$preferences.get(preferencesKey.value, {});
}

function refreshPresets() {
    presets.value = getPresets();
}

function viewAll() {
    activePreset.value = null;
    clearFilters();
    clearSearchQuery();
}

function selectPreset(handle) {
    activePreset.value = handle;
    setFilters(activePresetPayload.value.filters);
    setSearchQuery(activePresetPayload.value.query);
}

function createPreset() {
    savingPresetName.value = null;
    isCreating.value = true;
}

function canRenamePreset(handle) {
    return !Statamic.$preferences.hasDefault(`${preferencesKey.value}.${handle}`);
}

function canDeletePreset(handle) {
    return canRenamePreset(handle);
}

function renamePreset() {
    savingPresetName.value = activePresetPayload.value.display;
    isRenaming.value = true;
}

const canSaveNewPreset = computed(() => {
    return !activePreset.value && (Object.keys(activeFilters.value).length > 0 || (searchQuery.value ?? '') !== '');
});

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

const presetPreferencesPayload = computed(() => {
    let payload = {
        display: savingPresetName.value || activePresetPayload.value.display || '',
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
            if (key === activePreset.value) {
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
        .remove(`${preferencesKey.value}.${activePreset.value}`)
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
    <div
        class="relative flex shrink-0 space-x-2 border-b border-gray-200 text-sm text-gray-400 dark:border-gray-700/50 dark:text-gray-500"
    >
        <PresetTrigger :active="!activePreset" @click="viewAll" :text="__('All')" />
        <PresetTrigger
            v-for="(preset, handle) in presets"
            :key="handle"
            :active="handle === activePreset"
            @click="selectPreset(handle)"
        >
            {{ preset.display }}
            <template v-if="handle === activePreset">
                <Dropdown class="w-48!">
                    <template #trigger>
                        <Button class="absolute top-1.5 -right-3" variant="ghost" size="xs" icon="ui/chevron-down" />
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
                            @click="isConfirmingDeletion = true"
                        />
                    </DropdownMenu>
                </Dropdown>
            </template>
        </PresetTrigger>
        <Button
            v-if="canSaveNewPreset"
            @click="createPreset"
            variant="ghost"
            size="sm"
            :text="__('New View')"
            icon="add-bookmark"
            class="relative top-0.5 [&_svg]:size-4"
        />
    </div>

    <confirmation-modal
        v-if="isCreating"
        :title="__('Create New View')"
        :buttonText="__('Create')"
        @cancel="isCreating = false"
        @confirm="saveNew"
    >
        <Input v-model="savingPresetName" @keydown.enter="saveNew" />

        <div v-if="presets && Object.keys(presets).includes(savingPresetHandle)">
            <small
                class="help-block mt-2 mb-0 text-red-500"
                v-text="__('messages.filters_view_already_exists')"
            ></small>
        </div>
    </confirmation-modal>

    <confirmation-modal
        v-if="isRenaming"
        :title="__('Rename View')"
        :buttonText="__('Rename')"
        @cancel="isRenaming = false"
        @confirm="saveExisting"
    >
        <Input v-model="savingPresetName" @keydown.enter="saveExisting" />

        <div
            v-if="
                Object.keys(presets)
                    .filter((preset) => preset !== activePreset)
                    .includes(savingPresetHandle)
            "
        >
            <small
                class="help-block mt-2 mb-0 text-red-500"
                v-text="__('messages.filters_view_already_exists')"
            ></small>
        </div>
    </confirmation-modal>

    <confirmation-modal
        v-if="isConfirmingDeletion"
        :title="__('Delete View')"
        :bodyText="__('Are you sure you want to delete this view?')"
        :buttonText="__('Delete')"
        danger
        @confirm="deletePreset"
        @cancel="isConfirmingDeletion = false"
    />
</template>
