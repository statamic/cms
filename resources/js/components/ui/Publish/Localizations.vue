<script setup>
import {
    Label,
    Combobox,
    Card,
    Panel,
    Subheading,
    Icon,
} from '@ui';
import {
    ComboboxAnchor,
    ComboboxContent,
    ComboboxGroup,
    ComboboxItem,
    ComboboxLabel,
    ComboboxPortal,
    ComboboxRoot,
    ComboboxTrigger,
    ComboboxViewport,
} from 'reka-ui';
import { computed, ref, useId } from 'vue';
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

const comboboxId = useId();
const dropdownOpen = ref(false);

const panelHeading = computed(() => props.heading || __('Working In'));

const activeLocalization = computed(() => {
    return props.localizations.find((localization) => localization.active);
});

const UNGROUPED_KEY = 'other';

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

const selectedLabel = computed(() => {
    const active = activeLocalization.value;

    if (!active) return '';

    return __(active.name);
});

const selectedGroup = computed(() => {
    const active = activeLocalization.value;

    return active?.group ? __(active.group) : null;
});

function selectLocalization(localization) {
    if (!localization) return;

    dropdownOpen.value = false;
    emit('selected', localization);
}
</script>

<template>
    <Panel v-if="localizations.length > 1" :heading="panelHeading" icon="globe-arrow">
        <Card class="p-3! space-y-1">
            <template v-if="hasNamedGroups">
                <Label
                    :for="comboboxId"
                    :text="panelHeading"
                    class="sr-only"
                />

                <ComboboxRoot
                    class="w-full"
                    :open="dropdownOpen"
                    :model-value="activeLocalization?.handle"
                    ignore-filter
                    @update:open="dropdownOpen = $event"
                    @update:model-value="(handle) => selectLocalization(localizations.find((l) => l.handle === handle))"
                >
                    <ComboboxAnchor class="block w-full">
                        <ComboboxTrigger
                            :id="comboboxId"
                            class="flex h-10 w-full cursor-pointer items-center justify-between rounded-lg border border-gray-300 bg-linear-to-b from-white to-gray-50 px-4 text-md text-gray-900 shadow-ui-sm focus-within:focus-outline dark:border-gray-700 dark:from-gray-850 dark:to-gray-900 dark:text-gray-300 dark:shadow-ui-md"
                        >
                            <span class="flex min-w-0 items-center gap-1.5 text-start">
                                <template v-if="selectedGroup">
                                    <span class="truncate">{{ selectedGroup }}</span>
                                    <Icon name="chevron-right" class="size-3.5 shrink-0 text-gray-400 dark:text-white/40" aria-hidden="true" />
                                </template>
                                <span class="truncate">{{ selectedLabel }}</span>
                            </span>
                            <Icon name="chevron-down" class="ms-1.5 size-4 text-gray-400 dark:text-white/40" aria-hidden="true" />
                        </ComboboxTrigger>
                    </ComboboxAnchor>

                    <ComboboxPortal>
                        <ComboboxContent
                            position="popper"
                            :side-offset="5"
                            class="z-(--z-index-above) w-[var(--reka-combobox-trigger-width)] max-h-[var(--reka-combobox-content-available-height)] overflow-hidden rounded-lg border border-gray-200 bg-white shadow-ui-sm dark:border-white/10 dark:bg-gray-800"
                        >
                            <ComboboxViewport class="max-h-[300px] space-y-1 overflow-y-auto py-2">
                                <template
                                    v-for="(group, groupIndex) in localizationGroups"
                                    :key="group.key"
                                >
                                    <ComboboxGroup class="px-2">
                                        <ComboboxLabel v-if="group.label" as-child>
                                            <Subheading
                                                size="sm"
                                                class="px-2 py-1.5 font-semibold uppercase tracking-wide"
                                                :text="__(group.label)"
                                            />
                                        </ComboboxLabel>

                                        <ComboboxItem
                                            v-for="option in group.localizations"
                                            :key="option.handle"
                                            :value="option.handle"
                                            :text-value="option.name"
                                            as="button"
                                            class="w-full cursor-pointer rounded-lg px-2 py-2 text-sm outline-hidden data-highlighted:bg-gray-100 dark:data-highlighted:bg-gray-700"
                                            :class="option.active ? 'bg-blue-100 dark:bg-gray-700' : ''"
                                        >
                                            <Localization :localization="option" :localizing />
                                        </ComboboxItem>
                                    </ComboboxGroup>

                                    <div
                                        v-if="groupIndex < localizationGroups.length - 1"
                                        class="mx-2 my-1 border-t border-gray-200 dark:border-gray-700"
                                        role="separator"
                                    />
                                </template>
                            </ComboboxViewport>
                        </ComboboxContent>
                    </ComboboxPortal>
                </ComboboxRoot>
            </template>

            <template v-else-if="localizations.length > 5">
                <Label
                    :for="comboboxId"
                    :text="panelHeading"
                    class="sr-only"
                />

                <Combobox
                    :id="comboboxId"
                    class="flex-1"
                    :options="localizations"
                    option-value="handle"
                    option-label="name"
                    :model-value="activeLocalization?.handle"
                    @update:modelValue="(handle) => selectLocalization(localizations.find(l => l.handle === handle))"
                >
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
