<script setup>
import Panel from '../Panel/Panel.vue';
import PanelHeader from '../Panel/Header.vue';
import Heading from '../Heading.vue';
import Subheading from '../Subheading.vue';
import Card from '../Card/Card.vue';

const props = defineProps({
    heading: { type: String, default: null },
    subheading: { type: String, default: null },
    /** Controls the heading level for the panel title */
    level: { type: [Number, null], default: null },
});
</script>

<template>
    <Panel>
        <PanelHeader v-if="heading">
            <Heading :level="level" v-html="heading" />
            <Subheading v-if="subheading" v-html="subheading" />
        </PanelHeader>
        <Card>
            <slot />
        </Card>
    </Panel>
</template>
