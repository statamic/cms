<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { RankList } from '@/components/ui';
import { __ } from '@/bootstrap/globals';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly, name } = Fieldtype.use(emit, props);
defineExpose(expose);

const options = computed(() =>
    Object.entries(props.config.options ?? {}).map(([value, label]) => ({
        value,
        label: label ? __(label) : value,
    })),
);
</script>

<template>
    <RankList
        :model-value="value ?? []"
        :options
        :name
        :disabled="config.disabled || isReadOnly"
        @update:model-value="update"
    />
</template>
