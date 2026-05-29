<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update } = Fieldtype.use(emit, props);
defineExpose(expose);
</script>

<template>
    <div>
        TODO
    </div>
</template>
