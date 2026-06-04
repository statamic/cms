<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { __ } from '@/bootstrap/globals';
import { Icon } from '@ui';
import { computed, ref, useTemplateRef } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly, defineReplicatorPreview } = Fieldtype.use(emit, props);

const firstControl = useTemplateRef('firstControl');

defineExpose({
    ...expose,
    focus: () => firstControl.value?.focus(),
});

const maxStars = computed(() => {
    const max = Number(props.config.max_stars);

    if (! Number.isFinite(max) || max < 1) {
        return 5;
    }

    return Math.min(Math.round(max), 10);
});

const allowHalfStars = computed(() => Boolean(props.config.allow_half_stars));

const stars = computed(() => Array.from({ length: maxStars.value }, (_, index) => index + 1));

const hoverRating = ref(null);

const normalizedValue = computed(() => {
    if (props.value == null || props.value === '') {
        return null;
    }

    const value = Number(props.value);

    if (! Number.isFinite(value)) {
        return null;
    }

    if (! allowHalfStars.value) {
        return Math.round(value);
    }

    return Math.round(value * 2) / 2;
});

const displayRating = computed(() => hoverRating.value ?? normalizedValue.value);

const isDisabled = computed(() => props.config.disabled || isReadOnly.value);

const ratingsEqual = (a, b) => Math.abs(a - b) < 0.001;

const select = (rating) => {
    if (isDisabled.value) {
        return;
    }

    update(normalizedValue.value !== null && ratingsEqual(normalizedValue.value, rating) ? null : rating);
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

const starFill = (star) => {
    const rating = displayRating.value;

    if (rating === null) {
        return 'empty';
    }

    if (rating >= star) {
        return 'full';
    }

    if (allowHalfStars.value && rating >= star - 0.5) {
        return 'half';
    }

    return 'empty';
};

const starIcon = (star) => {
    const fill = starFill(star);

    if (fill === 'half') {
        return 'star-half';
    }

    return 'star';
};

const starIconClass = (star) => {
    const fill = starFill(star);

    if (fill === 'empty') {
        return 'text-gray-300 dark:text-gray-600';
    }

    return 'text-amber-400 dark:text-amber-300';
};

const ratingLabel = (rating) => {
    if (allowHalfStars.value && rating % 1 !== 0) {
        return __(':rating of :max stars', { rating, max: maxStars.value });
    }

    return __(':count of :max stars', { count: rating, max: maxStars.value });
};

const isSelected = (rating) => normalizedValue.value !== null && ratingsEqual(normalizedValue.value, rating);

defineReplicatorPreview(() => {
    if (normalizedValue.value == null) {
        return null;
    }

    return `${normalizedValue.value}/${maxStars.value}`;
});

</script>

<template>
    <div
        role="radiogroup"
        :aria-label="config.display ? __(config.display) : undefined"
        class="inline-flex items-center gap-0.5"
        data-star-rating
        :data-allow-half-stars="allowHalfStars ? '' : undefined"
        @mouseleave="onMouseLeave"
    >
        <div
            v-for="star in stars"
            :key="star"
            class="relative size-7 shrink-0"
        >
            <Icon
                :name="starIcon(star)"
                class="pointer-events-none absolute inset-0 m-auto size-6"
                :class="starIconClass(star)"
                aria-hidden="true"
            />
            <div
                v-if="allowHalfStars"
                class="absolute inset-0 flex"
            >
                <button
                    :ref="star === 1 ? 'firstControl' : undefined"
                    type="button"
                    role="radio"
                    class="h-full w-1/2 rounded-s p-0 transition-colors disabled:cursor-not-allowed disabled:opacity-60"
                    :class="isDisabled ? 'cursor-default' : 'cursor-pointer hover:bg-gray-100/80 dark:hover:bg-gray-800/80'"
                    :aria-checked="isSelected(star - 0.5) ? 'true' : 'false'"
                    :aria-label="ratingLabel(star - 0.5)"
                    :disabled="isDisabled"
                    @click="select(star - 0.5)"
                    @mouseenter="onMouseEnter(star - 0.5)"
                    @focus="onMouseEnter(star - 0.5)"
                    @blur="onMouseLeave"
                />
                <button
                    type="button"
                    role="radio"
                    class="h-full w-1/2 rounded-e p-0 transition-colors disabled:cursor-not-allowed disabled:opacity-60"
                    :class="isDisabled ? 'cursor-default' : 'cursor-pointer hover:bg-gray-100/80 dark:hover:bg-gray-800/80'"
                    :aria-checked="isSelected(star) ? 'true' : 'false'"
                    :aria-label="ratingLabel(star)"
                    :disabled="isDisabled"
                    @click="select(star)"
                    @mouseenter="onMouseEnter(star)"
                    @focus="onMouseEnter(star)"
                    @blur="onMouseLeave"
                />
            </div>
            <button
                v-else
                :ref="star === 1 ? 'firstControl' : undefined"
                type="button"
                role="radio"
                class="absolute inset-0 rounded p-0 transition-colors disabled:cursor-not-allowed disabled:opacity-60"
                :class="isDisabled ? 'cursor-default' : 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800'"
                :aria-checked="isSelected(star) ? 'true' : 'false'"
                :aria-label="ratingLabel(star)"
                :disabled="isDisabled"
                @click="select(star)"
                @mouseenter="onMouseEnter(star)"
                @focus="onMouseEnter(star)"
                @blur="onMouseLeave"
            />
        </div>
    </div>
</template>
