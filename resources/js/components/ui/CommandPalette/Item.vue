<script setup>
import { onUnmounted } from 'vue';

const props = defineProps({
    category: { type: String },
    type: { type: String },
    icon: { type: String },
    when: { type: Function },
    text: { type: [String, Array] },
    url: { type: String },
    action: { type: Function },
    openNewTab: { type: Boolean },
    trackRecent: { type: Boolean },
    prioritize: { type: Boolean },
});

const command = Statamic.$commandPalette.add(props);

onUnmounted(() => command.remove());
</script>

<template>
    <slot
        :category="category"
        :type="type"
        :icon="icon"
        :when="when"
        :text="text"
        :url="url"
        :action="action"
    />
</template>
