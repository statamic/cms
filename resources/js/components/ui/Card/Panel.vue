<script setup>
import { Panel, PanelHeader, Heading, Subheading, Card } from '@statamic/ui';

const props = defineProps({
    heading: { type: String, default: null },
    subheading: { type: String, default: null },
});
</script>

<template>
    <Panel>
        <PanelHeader v-if="heading">
            <Heading v-html="heading" />
            <Subheading v-if="subheading" v-html="subheading" />
        </PanelHeader>
        <Card>
            <slot />
        </Card>
    </Panel>
</template>
