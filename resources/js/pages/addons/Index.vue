<script setup>
import Head from '@/pages/layout/Head.vue';
import AddonListing from '@/components/addons/Listing.vue';
import { Header, Button, DocsCallout, CommandPaletteItem } from '@ui';

defineProps({
    addons: Array,
    columns: Array,
});
</script>

<template>
    <Head :title="__('Addons')" />

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

    <AddonListing
        :initial-rows="addons"
        :initial-columns="columns"
    />

    <DocsCallout :topic="__('Addons')" url="addons" />
</template>
