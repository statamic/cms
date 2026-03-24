<script setup>
import CardPanel from '../Card/Panel.vue';

const props = defineProps({
    /** Heading text for the empty state menu. */
    heading: {
        type: String,
        required: true,
    },
    /** Optional description text below the heading. */
    description: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <CardPanel :heading="heading" class="max-w-md m-auto">
        <ul class="flex flex-wrap [:has(>&)]:p-1.5">
            <slot />
        </ul>
    </CardPanel>
</template>
