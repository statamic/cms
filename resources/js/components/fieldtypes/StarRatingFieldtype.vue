<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { StarRating } from '@/components/ui';
import { __ } from '@/bootstrap/globals';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly, name } = Fieldtype.use(emit, props);
defineExpose(expose);

const step = computed(() => props.config.step ?? 1);
const label = computed(() => (props.config.display ? __(props.config.display) : null));

// Submissions made through a front-end form come back as strings.
const rating = computed(() => (props.value === null || props.value === undefined || props.value === '' ? null : Number(props.value)));
</script>

<template>
    <StarRating
        :model-value="rating"
        :name
        :label
        :min="config.min ?? step"
        :max="config.max_stars ?? 5"
        :step
        :disabled="config.disabled || isReadOnly"
        @update:model-value="update"
    />
</template>
