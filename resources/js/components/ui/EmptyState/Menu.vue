<script setup>
import { CardPanel, Icon } from '@statamic/ui';

const props = defineProps({
    heading: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <CardPanel :heading="heading" class="max-w-md m-auto">
        <div class="flex flex-wrap [:has(>&)]:p-1.5">
            <slot />

            <div class="hidden first:flex justify-center items-center p-8 w-full">
                <Icon :name="svg || 'empty/content'" />
            </div>
        </div>
    </CardPanel>
</template>
