<template>
    <div v-if="sites.length > 1" class="flex h-full items-center animate-in fade-in duration-750 fill-mode-forwards" data-ui-global-site-selector>
        <Select
            :model-value="active"
            :options="sites"
            :searchable="false"
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
                <div class="size-4">
                    <Icon name="globe-arrow" class="text-gray-900 dark:text-white dark:opacity-50" />
                </div>
                <span class="block truncate">{{ __(':name Site', { name: option.name }) }}</span>
            </template>
        </Select>
    </div>
</template>

<script>
import { Icon, Select } from '@/components/ui';

export default {
    components: { Icon, Select },

    computed: {
        sites() {
            return Statamic.$config.get('sites');
        },

        active() {
            return Statamic.$config.get('selectedSite');
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
