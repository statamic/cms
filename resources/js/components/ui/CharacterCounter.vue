<script setup>
import { computed, ref, watch } from 'vue';
import { cva } from 'cva';

const emit = defineEmits(['update:text']);

const props = defineProps({
  text: { type: String, default: '' },
  limit: { type: Number, default: null },
  dangerZone: { type: Number, default: 20 }
});

// Character count calculations
const charCount = computed(() => props.text?.length || 0);
const charsRemaining = computed(() => props.limit - charCount.value);
const isOverLimit = computed(() => charsRemaining.value < 0);
const isNearLimit = computed(() => charsRemaining.value <= props.dangerZone && charsRemaining.value > 0);
const isAtOrOverLimit = computed(() => charsRemaining.value <= 0);

const tooltipText = computed(() => {
  return charCount.value + '/' + props.limit;
});

// Calculate circle fill percentage
const circleFillPercentage = computed(() => {
  if (isAtOrOverLimit.value) return 100; // Fill the circle completely
  return (charCount.value / props.limit) * 100;
});

// Animation state
const isAnimating = ref(false);
const prevCount = ref(charCount.value);

// Watch for count changes to trigger animation on hitting limit exactly
watch(() => charCount.value, (newCount, oldCount) => {
  const wasNotAtLimit = oldCount !== props.limit;
  const isNowAtLimit = newCount === props.limit;

  if (wasNotAtLimit && isNowAtLimit) {
    isAnimating.value = true;
    setTimeout(() => {
      isAnimating.value = false;
    }, 275); // Animation duration
  }

  prevCount.value = newCount;
});

const circleClasses = cva({
    base: 'absolute h-full w-full',
    variants: {
        color: {
            green: 'text-green-500',
            amber: 'text-amber-500',
            red: 'text-red-500',
            gray: 'text-gray-200 dark:text-gray-700'
        }
    }
});

</script>

<template>
    <ui-tooltip :text="tooltipText" position="top" delay-duration="0">
        <div class="relative size-6 flex items-center justify-center" :class="{ 'animate-pop': isAnimating }">
            <svg :class="circleClasses({ color: 'gray' })" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="40" fill="none" stroke="currentColor" stroke-width="8" />
            </svg>
            <svg :class="circleClasses({ color: isOverLimit ? 'red' : circleFillPercentage < 70 ? 'green' : circleFillPercentage < 90 ? 'amber' : 'red' }) + ' -rotate-90'" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="40" fill="none" stroke="currentColor" stroke-width="8"
                    stroke-dasharray="251.2" :stroke-dashoffset="251.2 - (251.2 * circleFillPercentage / 100)" stroke-linecap="round" />
                <line v-if="isOverLimit" x1="20" y1="20" x2="80" y2="80" stroke="currentColor" stroke-width="8" />
            </svg>
            <span v-if="isNearLimit" class="text-2xs z-10 absolute text-red-500">
                {{ charsRemaining }}
            </span>
        </div>
    </ui-tooltip>
</template>

<style scoped>
@keyframes pop {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.2); }
}
.animate-pop {
  animation: pop 0.275s cubic-bezier(0.4, 0, 0.6, 1);
}
</style>
