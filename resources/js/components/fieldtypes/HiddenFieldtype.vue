<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { Input } from '@ui';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const {
    name,
    isReadOnly,
    updateDebounced,
    expose
} = Fieldtype.use(emit, props);

defineExpose(expose);
</script>

<template>
    <Input
        :name="name"
        :id="id"
        :model-value="value"
        :read-only="isReadOnly"
        :direction="config.direction"
        @update:model-value="updateDebounced"
    />
</template>
