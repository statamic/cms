<template>
    <div class="site-selector dark:border-dark-900 flex h-full items-center ltr:mr-4 rtl:ml-4">
        <Select
            :options="sites"
            option-label="name"
            option-value="handle"
            :searchable="false"
            :model-value="active"
            :button-appearance="false"
            @update:model-value="selected"
        >
            <template #selected-option="{ option }">
                <div
                    class="anti text-gray dark:text-dark-100 dark:hover:text-dark-175 flex items-center text-sm text-[0.8125rem] font-medium hover:text-gray-800"
                >
                    <svg-icon name="sites" class="h-4 w-4 text-gray-500 ltr:mr-2 rtl:ml-2" />
                    <div class="whitespace-nowrap">{{ __(option.name) }}</div>
                </div>
            </template>
            <template #option="option">
                <div :class="{ 'text-gray-500': handle === active }">{{ __(option.name) }}</div>
            </template>
        </Select>
    </div>
</template>

<script>
import { Select } from '@statamic/ui';

export default {
    components: { Select },

    computed: {
        sites() {
            return Statamic.$config.get('sites');
        },

        active() {
            return Statamic.$config.get('selectedSite');
        },

        activeName() {
            return this.sites.find((s) => s.handle === this.active).name;
        },
    },

    methods: {
        selected(siteHandle) {
            if (siteHandle !== this.active) {
                window.location = cp_url(`select-site/${siteHandle}`);
            }
        },
    },
};
</script>
