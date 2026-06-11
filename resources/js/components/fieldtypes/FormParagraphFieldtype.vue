<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { Heading, Subheading, Text } from '@ui';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);
defineExpose(expose);
</script>

<template>
    <Text :text="__(config.text)" />
</template>
