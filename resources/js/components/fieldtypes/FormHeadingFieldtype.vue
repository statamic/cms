<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { Heading, Subheading } from '@ui';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);
defineExpose(expose);
</script>

<template>
    <div data-form-heading>
        <Heading
            :text="__(config.display)"
            size="xl"
            class="!font-bold"
        />
        <Subheading v-if="config.subheading" :text="__(config.subheading)" class="mt-1" />
    </div>
</template>
