<script setup>
import { CardPanel } from '@statamic/ui';

const props = defineProps({
    heading: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <CardPanel :heading="heading" class="max-w-md m-auto">
        <div class="flex flex-wrap [:has(>&)]:p-1.5">
            <slot />
        </div>
    </CardPanel>
</template>
