<script setup>
import { ref, onMounted } from 'vue';
import Head from '@/pages/layout/Head.vue';
import { Header, Button, DocsCallout, CommandPaletteItem, Listing, Badge, DropdownItem } from '@ui';

const props = defineProps({
    addons: Array,
    columns: Array,
});

const rows = ref(props.addons);

onMounted(() => {
    props.addons.forEach(addon => {
		if (addon.marketplace_url) {
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: [__('Browse the Marketplace'), addon.name],
				icon: 'external-link',
				url: addon.marketplace_url,
				openNewTab: true,
			});
		}
    });
});
</script>

<template>
    <Head :title="__('Addons')" />

    <div class="max-w-5xl max-w-wrapper mx-auto">
        <Header :title="__('Addons')" icon="addons">
            <CommandPaletteItem
                category="Actions"
                :text="__('Browse the Marketplace')"
                icon="external-link"
                url="https://statamic.com/addons"
                open-new-tab
                prioritize
                v-slot="{ text, url, icon }"
            >
                <Button
                    variant="primary"
                    :text="text"
                    :href="url"
                    :icon="icon"
                    target="_blank"
                />
            </CommandPaletteItem>
        </Header>

        <Listing
            :items="rows"
            :columns="columns"
            :allow-search="false"
            :allow-customizing-columns="false"
        >
            <template #cell-name="{ row: addon }">
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

        <DocsCallout :topic="__('Addons')" url="addons" />
    </div>
</template>
