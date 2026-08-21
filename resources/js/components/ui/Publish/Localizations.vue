<script setup>
import {
    Label,
    Combobox,
    Card,
    Panel,
    Subheading,
    Icon,
} from '@ui';
import { computed, ref, useId, watch, nextTick } from 'vue';
import fuzzysort from 'fuzzysort';
import Localization from './Localization.vue';
import {
    flatOptionsFromSiteGroups,
    groupItemsBySiteGroup,
    hasNamedSiteGroups,
    selectedSiteGroupLabel,
} from '@/util/site-groups.js';

const props = defineProps({
    localizations: {
        type: Array,
        required: true,
    },
    localizing: {
        type: [Boolean, String],
        default: false,
    },
    confirmingSwitch: {
        type: Boolean,
        default: false,
    },
    heading: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['selected']);

const comboboxId = useId();
const searchQuery = ref('');
const pendingHandle = ref(null);

const panelHeading = computed(() => props.heading || __('Working In'));

const activeLocalization = computed(() => {
    return props.localizations.find((localization) => localization.active);
});

function clearPendingIfStale() {
    if (!pendingHandle.value) return;
    if (pendingHandle.value === activeLocalization.value?.handle) {
        pendingHandle.value = null;
        return;
    }
    if (props.localizing || props.confirmingSwitch) return;

    pendingHandle.value = null;
}

watch(
    () => activeLocalization.value?.handle,
    (handle) => {
        if (handle && handle === pendingHandle.value) {
            pendingHandle.value = null;
        }
    },
);

watch(
    () => props.localizing,
    (localizing, wasLocalizing) => {
        if (wasLocalizing && !localizing) {
            clearPendingIfStale();
        }
    },
);

watch(
    () => props.confirmingSwitch,
    (confirming, wasConfirming) => {
        if (wasConfirming && !confirming) {
            clearPendingIfStale();
        }
    },
);

const selectedLocalization = computed(() => {
    if (pendingHandle.value) {
        return props.localizations.find((localization) => localization.handle === pendingHandle.value)
            ?? activeLocalization.value;
    }

    return activeLocalization.value;
});

const localizationGroups = computed(() => groupItemsBySiteGroup(props.localizations));

const hasNamedGroups = computed(() => hasNamedSiteGroups(localizationGroups.value));

const useCombobox = computed(() => hasNamedGroups.value || props.localizations.length > 5);

const comboboxOptions = computed(() => {
    const query = searchQuery.value;

    return flatOptionsFromSiteGroups(localizationGroups.value, {
        filterItems: (localizations) => query
            ? fuzzysort
                .go(query, localizations, { keys: ['name', 'group', 'handle'] })
                .map((result) => result.obj)
            : localizations,
    });
});

function selectLocalization(localization) {
    if (!localization) return;

    searchQuery.value = '';
    pendingHandle.value = localization.handle;
    emit('selected', localization);

    // Parent may abort without starting a switch (e.g. can't create missing localization).
    if (!localization.exists) {
        nextTick(() => {
            if (pendingHandle.value !== localization.handle) return;
            if (props.localizing || props.confirmingSwitch) return;
            if (activeLocalization.value?.handle !== localization.handle) {
                pendingHandle.value = null;
            }
        });
    }
}

function handleSearch(query) {
    searchQuery.value = query;
}

function selectedGroupLabel(option) {
    return selectedSiteGroupLabel(option, hasNamedGroups.value);
}
</script>

<template>
    <Panel v-if="localizations.length > 1" :heading="panelHeading" icon="globe-arrow">
        <Card class="p-3! space-y-1">
            <template v-if="useCombobox">
                <Label
                    :for="comboboxId"
                    :text="panelHeading"
                    class="sr-only"
                />

                <Combobox
                    :id="comboboxId"
                    class="flex-1"
                    :options="comboboxOptions"
                    option-value="handle"
                    option-label="name"
                    :model-value="selectedLocalization?.handle"
                    :virtualize="!hasNamedGroups"
                    ignore-filter
                    @search="handleSearch"
                    @update:modelValue="(handle) => selectLocalization(localizations.find(l => l.handle === handle))"
                >
                    <template #selected-option="{ option }">
                        <span class="flex min-w-0 items-center gap-1.5">
                            <template v-if="selectedGroupLabel(option)">
                                <span class="truncate">{{ selectedGroupLabel(option) }}</span>
                                <Icon name="chevron-right" class="size-3.5! text-gray-700 dark:text-white/70" aria-hidden="true" />
                            </template>
                            <span class="truncate">{{ __(option.name) }}</span>
                        </span>
                    </template>

                    <template #before-option="option">
                        <div
                            v-if="option._showGroupSeparator"
                            class="mx-2 mb-2.25 mt-0.75 border-t border-gray-200 dark:border-gray-700"
                            role="separator"
                        />
                        <Subheading
                            v-if="option._groupLabel"
                            size="sm"
                            class="px-2.5 pb-1 pt-1.5 font-semibold uppercase tracking-wide text-gray-950 text-2xs dark:text-gray-300"
                            :text="__(option._groupLabel)"
                        />
                    </template>

                    <template #option="option">
                        <Localization :localization="option" :localizing />
                    </template>
                </Combobox>
            </template>

            <div
                v-else
                role="group"
                :aria-label="panelHeading"
                class="space-y-1"
            >
                <button
                    v-for="option in localizations"
                    :key="option.handle"
                    class="w-full cursor-pointer px-4 py-2 text-sm rounded-lg"
                    :class="option.active ? 'dark:bg-gray-700 bg-blue-100' : 'dark:hover:bg-gray-800 hover:bg-gray-100'"
                    @click="selectLocalization(option)"
                >
                    <Localization :localization="option" :localizing />
                </button>
            </div>
        </Card>
    </Panel>
</template>
