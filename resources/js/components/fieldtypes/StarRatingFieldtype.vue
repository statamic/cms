<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { __ } from '@/bootstrap/globals';
import { Icon } from '@ui';
import { computed, ref, useTemplateRef } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly, defineReplicatorPreview } = Fieldtype.use(emit, props);

const firstStar = useTemplateRef('firstStar');

defineExpose({
    ...expose,
    focus: () => firstStar.value?.focus(),
});

const maxStars = computed(() => {
    const max = Number(props.config.max_stars);

    if (! Number.isFinite(max) || max < 1) {
        return 5;
    }

    return Math.min(Math.round(max), 10);
});

const stars = computed(() => Array.from({ length: maxStars.value }, (_, index) => index + 1));

const hoverRating = ref(null);

const displayRating = computed(() => hoverRating.value ?? props.value ?? null);

const isDisabled = computed(() => props.config.disabled || isReadOnly.value);

const select = (rating) => {
    if (isDisabled.value) {
        return;
    }

    update(props.value === rating ? null : rating);
};

const onMouseEnter = (rating) => {
    if (isDisabled.value) {
        return;
    }

    hoverRating.value = rating;
};

const onMouseLeave = () => {
    hoverRating.value = null;
};

const isFilled = (star) => displayRating.value !== null && star <= displayRating.value;

const starLabel = (star) => __(':count of :max stars', { count: star, max: maxStars.value });

defineReplicatorPreview(() => {
    if (props.value == null || props.value === '') {
        return null;
    }

    return `${props.value}/${maxStars.value}`;
});

</script>

<template>
    <div
        role="radiogroup"
        :aria-label="config.display ? __(config.display) : undefined"
        class="inline-flex items-center gap-0.5"
        data-star-rating
        @mouseleave="onMouseLeave"
    >
        <button
            v-for="star in stars"
            :key="star"
            :ref="star === 1 ? 'firstStar' : undefined"
            type="button"
            role="radio"
            class="rounded p-0.5 transition-colors disabled:cursor-not-allowed disabled:opacity-60"
            :class="isDisabled ? 'cursor-default' : 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800'"
            :aria-checked="value === star ? 'true' : 'false'"
            :aria-label="starLabel(star)"
            :disabled="isDisabled"
            @click="select(star)"
            @mouseenter="onMouseEnter(star)"
            @focus="onMouseEnter(star)"
            @blur="onMouseLeave"
        >
            <Icon
                name="star"
                class="size-6"
                :class="isFilled(star) ? 'text-amber-400 dark:text-amber-300' : 'text-gray-300 dark:text-gray-600'"
            />
        </button>
    </div>
</template>
