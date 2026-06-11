<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { __ } from '@/bootstrap/globals';
import { Alert, Description, Heading } from '@ui';
import { computed } from 'vue';
import markdown from '@/util/markdown.js';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);
defineExpose(expose);

const text = computed(() => {
    if (!props.config.text) return null;
    return markdown(__(props.config.text), { openLinksInNewTabs: true });
});

const hasContent = computed(() => props.config.display || text.value);
</script>

<template>
    <div data-form-banner>
        <Alert
            v-if="hasContent"
            :icon="config.icon"
            :icon-fallback="false"
            class="shadow-ui-xs"
        >
            <Heading v-if="config.display" :text="__(config.display)" size="xl" />
            <Description v-if="text" :text="text" />
        </Alert>
        <Alert v-else :icon-fallback="false" :text="__('Add a label or help text to display a banner.')" />
    </div>
</template>
