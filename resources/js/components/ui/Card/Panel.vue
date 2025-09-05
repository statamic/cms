<script setup>
import Panel from '@ui/Panel/Panel.vue';
import PanelHeader from '@ui/Panel/Header.vue';
import Heading from '@ui/Heading.vue';
import Subheading from '@ui/Subheading.vue';
import Card from '@ui/Card/Card.vue';

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
