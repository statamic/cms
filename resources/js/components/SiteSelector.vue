<script setup>
import { computed } from 'vue';
import { Badge, Icon, Select, Subheading } from '@/components/ui';
import {
    flatOptionsFromSiteGroups,
    groupItemsBySiteGroup,
    hasNamedSiteGroups,
    selectedSiteGroupLabel,
} from '@/util/site-groups.js';

const props = defineProps({
    sites: { type: Array, required: true },
    modelValue: { type: String, required: true },
});

defineEmits(['update:modelValue']);

const hasNamedGroups = computed(() => hasNamedSiteGroups(props.sites));

const options = computed(() => {
    if (!hasNamedGroups.value) {
        return props.sites;
    }

    return flatOptionsFromSiteGroups(groupItemsBySiteGroup(props.sites));
});

function groupLabel(option) {
    return selectedSiteGroupLabel(option, hasNamedGroups.value);
}
</script>

<template>
    <Select
        class="w-60"
        :options="options"
        option-label="name"
        option-value="handle"
        align="end"
        :adaptive-width="true"
        :virtualize="!hasNamedGroups"
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <template #selected-option="{ option }">
            <span v-if="option" class="flex min-w-0 items-center gap-1.5">
                <template v-if="groupLabel(option)">
                    <span class="truncate">{{ groupLabel(option) }}</span>
                    <Icon name="chevron-right" class="size-3.5! shrink-0 text-gray-700 dark:text-white/70" aria-hidden="true" />
                </template>
                <span class="truncate">{{ __(option.name) }}</span>
                <span
                    v-if="option.fully_synced === true"
                    class="inline-flex shrink-0 ps-0.5"
                    v-tooltip="__('messages.localization_fully_synced_with_origin')"
                >
                    <Icon name="synced" class="size-3.5! text-gray-400 dark:text-gray-600" />
                </span>
            </span>
        </template>

        <template #option="option">
            <span class="flex min-w-0 flex-1 items-center gap-x-2">
                <span class="flex min-w-0 items-center gap-1.5">
                    <span class="truncate">{{ __(option.name) }}</span>
                    <span
                        v-if="option.fully_synced === true"
                        class="inline-flex shrink-0"
                        v-tooltip="__('messages.localization_fully_synced_with_origin')"
                    >
                        <Icon name="synced" class="size-3.5! text-gray-400 dark:text-gray-600" />
                    </span>
                </span>
                <Badge size="sm" color="orange" v-if="option.origin === true" :text="__('Origin')" />
                <Badge size="sm" color="blue" v-if="option.active === true" :text="__('Active')" />
                <Badge size="sm" color="purple" v-if="option.root === true && option.origin !== true && option.active !== true" :text="__('Root')" />
            </span>
        </template>

        <template v-if="hasNamedGroups" #before-option="option">
            <div
                v-if="option._showGroupSeparator"
                class="mx-2 mb-2.25 mt-0.75 border-t border-gray-200 dark:border-gray-700"
                role="separator"
            />
            <Subheading
                v-if="option._groupLabel"
                size="sm"
                class="px-2.5 pb-1 pt-1.5 font-semibold uppercase tracking-wide text-gray-950 text-2xs dark:text-gray-300"
                :text="option._groupLabel"
            />
        </template>
    </Select>
</template>
