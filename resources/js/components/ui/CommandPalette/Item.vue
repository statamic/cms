<script setup>
import { onUnmounted } from 'vue';
import { commandPalette } from '@api';

const props = defineProps({
    category: { type: String },
    icon: { type: String },
    when: { type: Function },
    text: { type: [String, Array] },
    url: { type: String },
    action: { type: Function },
    openNewTab: { type: Boolean },
    badge: { type: String },
    keys: { type: String },
    trackRecent: { type: Boolean },
    prioritize: { type: Boolean },
});

const command = commandPalette.add(props);

onUnmounted(() => command.remove());
</script>

<template>
    <slot
        :category="category"
        :icon="icon"
        :when="when"
        :text="text"
        :url="url"
        :action="action"
        :badge="badge"
        :keys="keys"
    />
</template>
