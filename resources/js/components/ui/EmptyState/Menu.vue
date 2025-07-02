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
    <CardPanel :heading="heading" class="m-auto max-w-md">
        <div class="flex flex-wrap [:has(>&)]:p-1.5">
            <slot />

            <div class="hidden w-full items-center justify-center p-8 first:flex">
                <Icon :name="svg || 'empty/content'" />
            </div>
        </div>
    </CardPanel>
</template>
