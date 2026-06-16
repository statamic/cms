<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { __ } from '@/bootstrap/globals';
import { computed, ref } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly, name } = Fieldtype.use(emit, props);
defineExpose(expose);

const step = computed(() => props.config.step ?? 1);
const min = computed(() => props.config.min ?? step.value);
const maxStars = computed(() => props.config.max_stars ?? 5);
const isDisabled = computed(() => props.config.disabled || isReadOnly.value);
const ariaLabel = computed(() => __(props.config.display ?? 'Star rating'));
// Hide fill until the user interacts; value still sits at min for correct thumb alignment.
const hasInteracted = ref(Boolean(props.value));
const isUnrated = computed(() => ! props.value && ! hasInteracted.value);

function onKeydown(event) {
    const navigationKeys = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];

    if (! navigationKeys.includes(event.key)) {
        return;
    }

    if (isUnrated.value) {
        if (event.key === 'End') {
            // Reveal fill but let the browser jump to max.
            hasInteracted.value = true;

            return;
        }

        // Thumb is already at min — stop the browser jumping to min + step.
        event.preventDefault();
        hasInteracted.value = true;
        update(min.value);

        return;
    }

    hasInteracted.value = true;
}
</script>

<template>
    <div class="show-focus-within w-fit rounded-xs ps-1.25 -ms-1.25" style="--focus-outline-offset: 0.1rem">
        <input
            type="range"
            class="star-rating-input show-focus-within_target focus-visible:outline-hidden"
            :class="{ 'star-rating-input--unrated': !value && !hasInteracted }"
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
            @pointerdown="hasInteracted = true"
            @keydown="onKeydown"
            @input="update(Number($event.target.value))"
        />
    </div>
</template>
