<template>
    <div class="site-selector flex h-full items-center dark:border-dark-900 ltr:mr-4 rtl:ml-4">
        <Combobox
            :options="sites"
            option-label="name"
            option-value="handle"
            :searchable="false"
            :model-value="active"
            :buttonAppearance="false"
            @update:model-value="selected"
        >
            <template #selected-option="{ option }">
                <div
                    class="anti flex items-center text-sm text-gray text-[0.8125rem] hover:text-gray-800 dark:text-dark-100 dark:hover:text-dark-175"
                >
                    <svg-icon name="sites" class="h-4 w-4 ltr:mr-2 rtl:ml-2 text-gray-500" />
                    <div class="whitespace-nowrap">{{ __(option.name) }}</div>
                </div>
            </template>
            <template #option="option">
                <div :class="{ 'text-gray-500': handle === active }">{{ __(option.name) }}</div>
            </template>
        </Combobox>
    </div>
</template>

<script>
import { Combobox } from '@statamic/ui';

export default {
    components: { Combobox },

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
