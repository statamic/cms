<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { __ } from '@/bootstrap/globals';
import { Description, Label } from '@ui';
import { computed } from 'vue';
import markdown from '@/util/markdown.js';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update } = Fieldtype.use(emit, props);

defineExpose(expose);

const instructions = computed(() => {
    if (!props.config.instructions) {
        return null;
    }

    return markdown(__(props.config.instructions), { openLinksInNewTabs: true });
});
</script>

<template>
    <div data-form-paragraph>
        <Label v-if="config.display" :text="__(config.display)" />
        <Description v-if="instructions" :text="instructions" :class="config.display ? 'mt-1.5' : null" />
    </div>
</template>
