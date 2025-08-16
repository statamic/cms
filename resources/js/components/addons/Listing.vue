<script setup>
import { ref } from 'vue';
import { Badge, DropdownItem, Listing } from '@statamic/cms/ui';

const props = defineProps(['initialRows', 'initialColumns']);
const rows = ref(props.initialRows);
const columns = ref(props.initialColumns);
</script>

<template>
    <Listing
        :items="rows"
        :columns="columns"
        :allow-search="false"
        :allow-customizing-columns="false"
    >
        <template #cell-name="{ row: addon, index }">
            <a v-if="addon.marketplace_url" :href="addon.marketplace_url" target="_blank">{{ __(addon.name) }}</a>
            <span v-else>
                {{ __(addon.name) }}
                <Badge class="ml-1" size="sm" :text="__('Unlisted')" />
            </span>
        </template>
        <template #cell-version="{ value: handle }">
            <span class="font-mono text-xs">{{ handle }}</span>
        </template>
        <template #prepended-row-actions="{ row: addon }">
            <DropdownItem v-if="addon.marketplace_url" :text="__('View on the Marketplace')" icon="external-link" :href="addon.marketplace_url" target="_blank" />
            <DropdownItem v-if="addon.updates_url" :text="__('Release Notes')" icon="updates" :href="addon.updates_url" />
            <DropdownItem v-if="addon.settings_url" :text="__('Settings')" icon="cog" :href="addon.settings_url" />
        </template>
    </Listing>
</template>
