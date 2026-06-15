<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { __ } from '@/bootstrap/globals';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly, name } = Fieldtype.use(emit, props);
defineExpose(expose);

const step = computed(() => props.config.step ?? props.meta.step ?? 1);
const min = computed(() => props.config.min ?? props.meta.min ?? step.value);
const maxStars = computed(() => props.config.max_stars ?? props.meta.max_stars ?? 5);
const isDisabled = computed(() => props.config.disabled || isReadOnly.value);
const ariaLabel = computed(() => __(props.config.display ?? 'Star rating'));
</script>

<template>
    <input
        type="range"
        class="star-rating-input"
        :class="{ 'star-rating-input--unrated': !value }"
        data-star-rating
        :name="name"
        :min="min"
        :max="maxStars"
        :step="step"
        :value="value || min"
        :disabled="isDisabled"
        :aria-label="ariaLabel"
        :style="{
            '--star-rating-max': maxStars,
            '--star-rating-step': step,
        }"
        @input="update(Number($event.target.value))"
    />
</template>
