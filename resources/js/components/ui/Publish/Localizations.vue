<script setup>
import {
    Label,
    Combobox,
    Card,
    Panel,
    Subheading,
    Icon,
} from '@ui';
import { computed, ref, useId, watch } from 'vue';
import fuzzysort from 'fuzzysort';
import Localization from './Localization.vue';

const props = defineProps({
    localizations: {
        type: Array,
        required: true,
    },
    localizing: {
        type: [Boolean, String],
        default: false,
    },
    heading: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['selected']);

const UNGROUPED_KEY = 'other';

const comboboxId = useId();
const searchQuery = ref('');
const pendingHandle = ref(null);

const panelHeading = computed(() => props.heading || __('Working In'));

const activeLocalization = computed(() => {
    return props.localizations.find((localization) => localization.active);
});

watch(
    () => activeLocalization.value?.handle,
    (handle) => {
        if (handle && handle === pendingHandle.value) {
            pendingHandle.value = null;
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

const localizationGroups = computed(() => {
    const groups = [];
    const indexByKey = new Map();

    for (const localization of props.localizations) {
        const isUngrouped = !localization.group && !localization.group_handle;
        const key = isUngrouped
            ? UNGROUPED_KEY
            : (localization.group_handle || localization.group);
        const label = isUngrouped ? __('Other') : localization.group;

        if (!indexByKey.has(key)) {
            indexByKey.set(key, groups.length);
            groups.push({ key, label, localizations: [] });
        }

        groups[indexByKey.get(key)].localizations.push(localization);
    }

    const named = groups.filter((group) => group.key !== UNGROUPED_KEY);
    const other = groups.filter((group) => group.key === UNGROUPED_KEY);

    return [...named, ...other];
});

const hasNamedGroups = computed(() => localizationGroups.value.some((group) => group.key !== UNGROUPED_KEY));

const useCombobox = computed(() => hasNamedGroups.value || props.localizations.length > 5);

const comboboxOptions = computed(() => {
    const query = searchQuery.value;
    let isFirstVisibleOption = true;

    return localizationGroups.value.flatMap((group) => {
        const localizations = query
            ? fuzzysort
                .go(query, group.localizations, { keys: ['name', 'group', 'handle'] })
                .map((result) => result.obj)
            : group.localizations;

        if (!localizations.length) {
            return [];
        }

        return localizations.map((localization, index) => {
            const option = {
                ...localization,
                _groupLabel: index === 0 ? group.label : null,
                _showGroupSeparator: index === 0 && !isFirstVisibleOption,
            };

            isFirstVisibleOption = false;

            return option;
        });
    });
});

function selectLocalization(localization) {
    if (!localization) return;

    searchQuery.value = '';
    pendingHandle.value = localization.handle;
    emit('selected', localization);
}

function handleSearch(query) {
    searchQuery.value = query;
}

function selectedGroupLabel(option) {
    if (option.group) {
        return __(option.group);
    }

    return hasNamedGroups.value ? __('Other') : null;
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
