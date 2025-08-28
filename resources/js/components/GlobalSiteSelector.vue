<template>
    <div class="flex h-full items-center">
        <Select
            :model-value="active"
            :options="sites"
            :searchable="false"
            @update:model-value="selected"
            option-label="name"
            option-value="handle"
            variant="filled"
        >
            <template #selected-option="{ option }">
                <div class="flex items-center gap-2 text-sm font-medium text-[0.8125rem] text-gray-300 hover:text-white">
                    <ui-icon name="globe-arrow" class="size-4" />
                    <div class="whitespace-nowrap">{{ __(option.name) }}</div>
                </div>
            </template>
            <template #option="option">
                <div class="whitespace-nowrap" :class="{ 'text-gray-500': handle === active }">{{ __(option.name) }}</div>
            </template>
        </Select>
    </div>
</template>

<script>
import { Select } from '@/components/ui';

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
