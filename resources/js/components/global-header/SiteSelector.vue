<template>
    <div v-if="sites.length > 1" class="flex h-full items-center animate-in fade-in duration-750 fill-mode-forwards" data-ui-global-site-selector>
        <Select
            :model-value="active"
            :options="comboboxOptions"
            :searchable="false"
            :virtualize="!hasNamedGroups"
            @update:model-value="selected"
            option-label="name"
            option-value="handle"
            size="sm"
            variant="ghost"
            align="end"
            :adaptive-width="true"
            class="[&_[data-ui-combobox-trigger]]:text-white/85"
        >
            <template #selected-option="{ option }">
                <div class="size-4 shrink-0">
                    <Icon name="globe-arrow" class="text-gray-900 dark:text-white dark:opacity-50" />
                </div>
                <span class="flex min-w-0 items-center gap-1.5">
                    <template v-if="groupLabel(option)">
                        <span class="truncate">{{ groupLabel(option) }}</span>
                        <Icon name="chevron-right" class="size-3.5! opacity-75" aria-hidden="true" />
                    </template>
                    <span class="truncate">{{ __(':name Site', { name: option.name }) }}</span>
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
                    :text="__(option._groupLabel)"
                />
            </template>
        </Select>
    </div>
</template>

<script>
import { Icon, Select, Subheading } from '@/components/ui';
import {
    flatOptionsFromSiteGroups,
    groupItemsBySiteGroup,
    hasNamedSiteGroups,
    selectedSiteGroupLabel,
} from '@/util/site-groups.js';

export default {
    components: { Icon, Select, Subheading },

    computed: {
        sites() {
            return Statamic.$config.get('sites');
        },

        active() {
            return Statamic.$config.get('selectedSite');
        },

        hasNamedGroups() {
            return hasNamedSiteGroups(this.sites);
        },

        comboboxOptions() {
            if (!this.hasNamedGroups) {
                return this.sites;
            }

            return flatOptionsFromSiteGroups(groupItemsBySiteGroup(this.sites));
        },
    },

    methods: {
        groupLabel(option) {
            return selectedSiteGroupLabel(option, this.hasNamedGroups);
        },

        selected(siteHandle) {
            if (siteHandle !== this.active) {
                window.location = cp_url(`select-site/${siteHandle}`);
            }
        },
    },
};
</script>
