<template>
    <div class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-3 pr-2 py-2 dark:border-gray-700 dark:bg-gray-800">
            <Checkbox
                size="sm"
                solo
                :model-value="allSelected"
                :indeterminate="someSelected && !allSelected"
                :disabled="!sites.length"
                :label="__('Select all items')"
                @update:model-value="toggleSelectAll"
            />
            <Select
                class="flex-1 font-normal"
                :options="massOriginOptions"
                :clearable="true"
                :virtualize="!hasNamedGroups"
                :placeholder="__('Set origin to...')"
                :model-value="massOrigin"
                @update:model-value="applyMassOrigin"
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
                            :text="option._groupLabel"
                        />
                    </template>
                </template>
            </Select>
            <span v-if="selections.length" class="pe-1.5 text-sm text-gray-700 dark:text-gray-300">
                {{ __n(':count site selected|:count sites selected', selections.length) }}
            </span>
        </div>

        <table class="grid-table">
            <thead>
                <tr>
                    <th scope="col" class="checkbox-column w-8">
                        <span class="sr-only">{{ __('Select') }}</span>
                    </th>
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
                        <td colspan="3" class="sticky top-[calc(--spacing(7)+1px)] z-(--z-index-above) bg-gray-50 py-2! dark:bg-gray-800">
                            <div class="flex items-center gap-4 ps-1!">
                                <Checkbox
                                    size="sm"
                                    solo
                                    :model-value="isGroupSelected(group)"
                                    :indeterminate="isGroupPartiallySelected(group)"
                                    :label="__('Select :name', { name: group.label })"
                                    @update:model-value="toggleGroupSelection(group, $event)"
                                />
                                <Subheading
                                    size="sm"
                                    class="font-semibold uppercase tracking-wide text-gray-950 text-2xs dark:text-gray-300"
                                    :text="group.label"
                                />
                            </div>
                        </td>
                    </tr>
                    <tr v-for="site in group.items" :key="site.handle">
                        <td class="checkbox-column ps-3!">
                            <Checkbox
                                size="sm"
                                class="pt-2.5"
                                solo
                                :model-value="isSelected(site.handle)"
                                :label="__('Select :name', { name: __(site.name) })"
                                @update:model-value="toggleSelection(site.handle, $event)"
                            />
                        </td>
                        <td class="grid-cell">
                            <div class="flex pt-2 items-center gap-2">
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
                                @update:model-value="setSiteOrigin(site, $event)"
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
                                            :text="option._groupLabel"
                                        />
                                    </template>
                                </template>
                            </Select>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>

<script>
import Fieldtype from '../fieldtypes/Fieldtype.vue';
import { Checkbox, Icon, Switch, Heading, Select, Subheading } from '@/components/ui';
import {
    flatOptionsFromSiteGroups,
    groupItemsBySiteGroup,
    hasNamedSiteGroups,
    selectedSiteGroupLabel,
} from '@/util/site-groups.js';

export default {
    mixins: [Fieldtype],

    components: {
        Checkbox,
        Icon,
        Switch,
        Heading,
        Select,
        Subheading,
    },

    data() {
        return {
            sites: this.value ?? [],
            selections: [],
            massOrigin: null,
        };
    },

    computed: {
        hasNamedGroups() {
            return hasNamedSiteGroups(this.sites);
        },

        siteGroups() {
            return groupItemsBySiteGroup(this.sites ?? []);
        },

        allSelected() {
            return this.sites.length > 0
                && this.sites.every((site) => this.selections.includes(site.handle));
        },

        someSelected() {
            return this.sites.some((site) => this.selections.includes(site.handle));
        },

        massOriginOptions() {
            return this.originOptions();
        },
    },

    watch: {
        value(value) {
            this.sites = value ?? [];
        },

        sites: {
            deep: true,
            handler(sites) {
                this.pruneSelections(sites);
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

        originOptions(excludeHandle = null) {
            const options = (this.sites ?? [])
                .filter((site) => site.handle !== excludeHandle)
                .map((site) => ({
                    value: site.handle,
                    label: __(site.name),
                    group: site.group,
                    group_handle: site.group_handle,
                }));

            if (!this.hasNamedGroups) {
                return options;
            }

            return flatOptionsFromSiteGroups(groupItemsBySiteGroup(options));
        },

        siteOriginOptions(site) {
            return this.originOptions(site.handle);
        },

        isSelected(handle) {
            return this.selections.includes(handle);
        },

        groupHandles(group) {
            return (group?.items ?? []).map((site) => site.handle);
        },

        isGroupSelected(group) {
            const handles = this.groupHandles(group);

            return handles.length > 0
                && handles.every((handle) => this.selections.includes(handle));
        },

        isGroupPartiallySelected(group) {
            const handles = this.groupHandles(group);
            const selectedCount = handles.filter((handle) => this.selections.includes(handle)).length;

            return selectedCount > 0 && selectedCount < handles.length;
        },

        toggleGroupSelection(group, selected) {
            const handles = this.groupHandles(group);

            if (selected) {
                this.selections = [...new Set([...this.selections, ...handles])];

                return;
            }

            const remove = new Set(handles);

            this.selections = this.selections.filter((handle) => !remove.has(handle));
        },

        toggleSelection(handle, selected) {
            if (selected) {
                if (!this.selections.includes(handle)) {
                    this.selections.push(handle);
                }

                return;
            }

            this.selections = this.selections.filter((value) => value !== handle);
        },

        toggleSelectAll(selected) {
            if (!selected) {
                this.clearSelections();

                return;
            }

            this.selections = this.sites.map((site) => site.handle);
        },

        clearSelections() {
            this.selections = [];
            this.massOrigin = null;
        },

        pruneSelections(sites) {
            const handles = new Set((sites ?? []).map((site) => site.handle));

            this.selections = this.selections.filter((handle) => handles.has(handle));
        },

        setSiteOrigin(site, origin) {
            if (origin && this.wouldCreateOriginCycle(site.handle, origin)) {
                this.$toast.error(__('Origin sites cannot reference each other in a loop.'));

                return;
            }

            site.origin = origin;

            if (!origin) {
                return;
            }

            site.enabled = true;

            const originSite = this.sites.find((item) => item.handle === origin);

            if (originSite) {
                originSite.enabled = true;
            }
        },

        applyMassOrigin(origin) {
            this.massOrigin = origin;

            if (!origin || !this.selections.length) {
                this.massOrigin = null;

                return;
            }

            const selected = new Set(this.selections);
            const originSite = this.sites.find((site) => site.handle === origin);

            if (originSite) {
                originSite.enabled = true;
            }

            const blocked = this.sites.some(
                (site) => selected.has(site.handle)
                    && site.handle !== origin
                    && this.wouldCreateOriginCycle(site.handle, origin),
            );

            if (blocked) {
                this.$toast.error(__('Origin sites cannot reference each other in a loop.'));
                this.massOrigin = null;

                return;
            }

            this.sites.forEach((site) => {
                if (!selected.has(site.handle) || site.handle === origin) {
                    return;
                }

                site.enabled = true;
                site.origin = origin;
            });

            this.massOrigin = null;
            this.clearSelections();
        },

        wouldCreateOriginCycle(siteHandle, originHandle) {
            const origins = Object.fromEntries(
                (this.sites ?? []).map((site) => [site.handle, site.origin]),
            );

            origins[siteHandle] = originHandle;

            const seen = {};
            let current = siteHandle;

            while (current) {
                if (seen[current]) {
                    return true;
                }

                seen[current] = true;
                current = origins[current] || null;
            }

            return false;
        },
    },
};
</script>
