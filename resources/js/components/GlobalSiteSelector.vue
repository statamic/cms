<template>
    <div class="flex h-full items-center animate-in fade-in duration-750 fill-mode-forwards" data-ui-global-site-selector>
        <Select
            :model-value="active"
            :options="sites"
            :searchable="false"
            @update:model-value="selected"
            option-label="name"
            option-value="handle"
            size="sm"
            variant="ghost"
            icon="globe-arrow"
            class="[&_[data-ui-combobox-trigger]]:text-white/85"
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
