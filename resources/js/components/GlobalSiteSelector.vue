<template>
    <div class="flex h-full items-center" data-ui-global-site-selector>
        <Select
            :model-value="active"
            :options="sites"
            :searchable="false"
            @update:model-value="selected"
            option-label="name"
            option-value="handle"
            size="sm"
            icon="globe-arrow"
        />
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

<style>
[data-ui-global-site-selector] [data-ui-combobox-trigger] {
    background: color-mix(in srgb, var(--theme-color-global-header-bg) 70%, black 80%) !important;
    border: transparent !important;
}
</style>
