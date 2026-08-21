<template>
    <table class="grid-table">
        <thead>
            <tr>
                <th scope="col">
                    <div class="flex items-center justify-between">
                        {{ __('Site') }}
                    </div>
                </th>
                <th scope="col">
                    <div class="flex items-center justify-between">
                        {{ __('Origin') }}
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            <template v-for="group in siteGroups" :key="group.key">
                <tr v-if="hasNamedGroups">
                    <td colspan="2" class="bg-gray-50 dark:bg-gray-800 !py-2">
                        <Subheading
                            size="sm"
                            class="px-1 font-semibold uppercase tracking-wide text-gray-950 text-2xs dark:text-gray-300"
                            :text="__(group.label)"
                        />
                    </td>
                </tr>
                <tr v-for="site in group.items" :key="site.handle">
                    <td class="grid-cell">
                        <div class="flex items-center gap-2">
                            <Switch v-model="site.enabled" />
                            <Heading :text="__(site.name)" />
                        </div>
                    </td>
                    <td class="grid-cell">
                        <Select
                            class="w-full"
                            :options="siteOriginOptions(site)"
                            :clearable="true"
                            :virtualize="!hasNamedGroups"
                            :model-value="site.origin"
                            @update:model-value="site.origin = $event"
                        >
                            <template #selected-option="{ option }">
                                <span v-if="option" class="flex min-w-0 items-center gap-1.5">
                                    <template v-if="originGroupLabel(option)">
                                        <span class="truncate">{{ originGroupLabel(option) }}</span>
                                        <Icon name="chevron-right" class="size-3.5! shrink-0 text-gray-700 dark:text-white/70" aria-hidden="true" />
                                    </template>
                                    <span class="truncate">{{ option.label }}</span>
                                </span>
                            </template>

                            <template #before-option="option">
                                <template v-if="hasNamedGroups">
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
                            </template>
                        </Select>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</template>

<script>
import Fieldtype from '../fieldtypes/Fieldtype.vue';
import { Icon, Switch, Heading, Select, Subheading } from '@/components/ui';
import {
    flatOptionsFromSiteGroups,
    groupItemsBySiteGroup,
    hasNamedSiteGroups,
    selectedSiteGroupLabel,
} from '@/util/site-groups.js';

export default {
    mixins: [Fieldtype],

    components: {
        Icon,
        Switch,
        Heading,
        Select,
        Subheading,
    },

    data() {
        return {
            sites: this.value ?? [],
        };
    },

    computed: {
        hasNamedGroups() {
            return hasNamedSiteGroups(this.sites);
        },

        siteGroups() {
            return groupItemsBySiteGroup(this.sites ?? []);
        },
    },

    watch: {
        value(value) {
            this.sites = value ?? [];
        },

        sites: {
            deep: true,
            handler(sites) {
                this.update(sites);
            },
        },
    },

    methods: {
        originGroupLabel(option) {
            if (!option || !this.hasNamedGroups) {
                return null;
            }

            return selectedSiteGroupLabel(option, true);
        },

        siteOriginOptions(site) {
            const options = (this.sites ?? [])
                .filter((s) => s.handle !== site.handle)
                .map((s) => ({
                    value: s.handle,
                    label: __(s.name),
                    group: s.group,
                    group_handle: s.group_handle,
                }));

            if (!this.hasNamedGroups) {
                return options;
            }

            return flatOptionsFromSiteGroups(groupItemsBySiteGroup(options));
        },
    },
};
</script>
