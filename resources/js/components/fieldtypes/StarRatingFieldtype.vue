<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { __ } from '@/bootstrap/globals';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly, name } = Fieldtype.use(emit, props);
defineExpose(expose);

const step = computed(() => props.config.step ?? 1);
const maxStars = computed(() => props.config.max_stars ?? 5);
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
        :min="0"
        :max="maxStars"
        :step="step"
        :value="value ?? 0"
        :disabled="isDisabled"
        :aria-label="ariaLabel"
        :style="{
            '--star-rating-max': maxStars.value,
            '--star-rating-step': step.value,
        }"
        @input="update(Number($event.target.value))"
    />
</template>
