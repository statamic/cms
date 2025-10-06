<script setup>
import { CommandPaletteItem, Badge } from '@ui';
import { computed } from 'vue';

const props = defineProps({
    topic: { type: String, required: true },
    url: { type: String, required: true },
});

const linkToDocs = Statamic.$config.get('linkToDocs');
const url = computed(() => props.url.startsWith('http') ? props.url : `https://statamic.dev/${props.url}`);
</script>

<template>

    <div v-if="linkToDocs" class="mt-12 flex justify-center text-center starting-style-transition">
        <CommandPaletteItem
            :text="[__('Statamic Documentation'), topic]"
            icon="book-next-page"
            :url="url"
            open-new-tab
            track-recent
            v-slot="{ url, icon }"
        >
            <Badge
                :text="__('Learn about :topic', { topic })"
                icon-append="external-link"
                :href="url"
                target="_blank"
                pill
            />
        </CommandPaletteItem>
    </div>

</template>
